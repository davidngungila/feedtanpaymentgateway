<?php

namespace App\Services\MobileMoney\Mixx;

use App\Models\Customer;
use App\Models\Disbursement;
use App\Models\Payment;
use App\Models\ProviderTransaction;
use App\Payments\PaymentEngine;
use App\Support\Security\Audit;
use App\Support\Security\Crypto;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Mixx by Yas W2A collection + A2W disbursement orchestration.
 *
 * - Unique FEEDTAN references (FTP-YYYYMMDD-000001).
 * - Idempotent: resubmitting the same idempotency key returns the
 *   existing transaction instead of charging twice.
 * - Attempts are appended, never overwritten (payment_attempts).
 * - error111 / timeouts / TXNSTATUS 100 become UNKNOWN / HOLD,
 *   resolved later by verification — never blind FAILED, never blind retry.
 * - Amount must confirm exactly or the record is flagged AMOUNT_MISMATCH.
 */
class MixxTransactionService
{
    public function __construct(protected MixxConfig $config, protected MixxClient $client) {}

    public static function make(string $environment = 'live'): self
    {
        $config = MixxConfig::make($environment);

        return new self($config, new MixxClient($config));
    }

    /**
     * W2A collection: wallet -> FEEDTAN collection account.
     *
     * @param  array{msisdn:string,amount:mixed,customer_name?:string,customer_reference?:string,sender_name?:string,currency?:string}  $input
     * @return array{success:bool,transaction?:ProviderTransaction,error?:string,http_status:int}
     */
    public function collect(array $input, $actor = null): array
    {
        try {
            $msisdn = MixxValidator::msisdn($input['msisdn'] ?? '');
            $amount = MixxValidator::amount($input['amount'] ?? 0, $this->config);
        } catch (InvalidArgumentException $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'http_status' => 422];
        }

        $customerName = trim($input['customer_name'] ?? 'Mwanachama');
        $senderName = trim($input['sender_name'] ?? $customerName);
        $customerRef = trim($input['customer_reference'] ?? '');
        if ($customerRef === '') {
            $customerRef = $this->nextFeedtanReference();
        }

        $internalRef = $this->nextFeedtanReference('FTX');
        $idempotencyKey = 'mixx:'.$internalRef;

        $existing = ProviderTransaction::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return ['success' => $existing->status === 'SUCCESS', 'transaction' => $existing, 'http_status' => 200];
        }

        if (! $this->config->isConfigured()) {
            return ['success' => false, 'error' => 'Mixx integration is not configured yet (base URL + initiate path).', 'http_status' => 503];
        }

        try {
            $result = DB::transaction(function () use ($msisdn, $amount, $customerName, $senderName, $customerRef, $internalRef, $idempotencyKey, $actor, $input) {
                $customer = Customer::findOrCreateFromPayment($customerName, $msisdn);
                $payment = Payment::create([
                    'reference' => $internalRef,
                    'gateway' => 'direct',
                    'provider' => 'mixx',
                    'method' => 'mobile_money',
                    'channel' => 'W2A',
                    'customer_id' => $customer->id,
                    'customer_name' => $customerName,
                    'customer_phone' => $msisdn,
                    'customer_country' => 'TZ',
                    'amount' => $amount,
                    'fee' => 0,
                    'net_amount' => $amount,
                    'currency' => $input['currency'] ?? $this->config->currency(),
                    'status' => 'pending',
                    'notes' => $customerRef,
                    'description' => $customerRef,
                    'settlement_status' => 'pending',
                    'webhook_status' => 'pending',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'operator_id' => $actor?->id,
                ]);

                $txn = ProviderTransaction::create([
                    'txn_id' => 'TXN-'.strtoupper(\Illuminate\Support\Str::random(10)),
                    'internal_reference' => $internalRef,
                    'provider' => 'mixx',
                    'customer_id' => $customer->id,
                    'payment_id' => $payment->id,
                    'phone_hash' => Crypto::blindIndex('customer', $msisdn),
                    'msisdn_encrypted' => $msisdn,
                    'phone_last4' => MixxValidator::last4($msisdn),
                    'sender_name_encrypted' => $senderName,
                    'customer_reference_id' => $customerRef,
                    'amount' => $amount,
                    'expected_amount' => $amount,
                    'currency' => $payment->currency,
                    'reference' => $internalRef,
                    'provider_fee' => 0,
                    'net_amount' => $amount,
                    'status' => 'PENDING',
                    'initiated_at' => now(),
                    'webhook_status' => 'pending',
                    'settlement_status' => 'pending',
                    'idempotency_key' => $idempotencyKey,
                ]);

                return [$payment, $txn];
            });
        } catch (\Throwable $e) {
            report($e);

            return ['success' => false, 'error' => 'Could not create collection: '.$e->getMessage(), 'http_status' => 500];
        }

        /** @var Payment $payment */
        /** @var ProviderTransaction $txn */
        [$payment, $txn] = $result;

        // Attempt #1 — send SYNC_BILLPAY_REQUEST.
        $attemptNo = 1;
        $started = now();
        $payment->recordAttempt('mixx', 'w2a.initiate', false, null, [
            'attempt_number' => $attemptNo, 'request_id' => $internalRef, 'status' => 'SENDING', 'started_at' => $started,
        ]);

        $fields = MixxRequestBuilder::billpay(
            $internalRef, $msisdn, $amount,
            $this->config->companyName(), $customerRef, $senderName
        );
        $send = $this->client->postXml(
            $this->config->initiatePath(), MixxRequestBuilder::toXml($fields),
            'w2a.initiate', $internalRef, $attemptNo
        );

        if (! $send['transport_ok']) {
            // Timeout / no response behaves like error111: UNKNOWN, verify later.
            $this->finishAttempt($payment, $attemptNo, $internalRef, false, 'UNKNOWN', 'error111', $send['error'], $started);
            $txn->update(['status' => 'UNKNOWN', 'exception_type' => 'TIMEOUT']);
            Audit::log('mixx.collect.timeout', $txn, ['reference' => $internalRef, 'phone' => $msisdn], 'FAILED', 'warning');

            return ['success' => false, 'transaction' => $txn->refresh(), 'error' => $send['error'].' Recorded as UNKNOWN — verification will resolve it.', 'http_status' => 504];
        }

        $parsed = MixxResponseParser::billpay($send['body']);
        $mapped = MixxErrorMapper::map('mixx', $parsed['errorcode'] ?? $parsed['result'] ?? '');
        $this->finishAttempt($payment, $attemptNo, $internalRef, $mapped['internal_status'] === 'SUCCESS', $mapped['internal_status'], $mapped['code'], $parsed['errordescription'] ?? $mapped['description'], $started);

        $updates = [
            'provider_transaction_id_encrypted' => $parsed['txnid'],
            'provider_transaction_id_hash' => $parsed['txnid'] ? Crypto::blindIndex('webhook', 'mixx:'.$parsed['txnid']) : null,
            'provider_refid_encrypted' => $parsed['refid'],
            'provider_refid_hash' => $parsed['refid'] ? Crypto::blindIndex('webhook', 'mixx:ref:'.$parsed['refid']) : null,
            'sender_name_confirmed' => $parsed['sendername'],
            'payload_hash' => Crypto::payloadHash($send['body']),
        ];

        if ($mapped['internal_status'] === 'SUCCESS') {
            $confirmed = is_numeric($parsed['amount'] ?? null) ? (float) $parsed['amount'] : null;
            if (! MixxValidator::confirmAmount($amount, $confirmed)) {
                $updates['status'] = 'SUCCESS';
                $updates['completed_at'] = now();
                $updates['exception_type'] = 'AMOUNT_MISMATCH';
                Audit::log('mixx.amount.mismatch', $txn, ['requested' => $amount, 'confirmed' => $confirmed], 'FAILED', 'warning');
            }
        }

        $txn->update($updates);
        $txn = $this->applyMappedStatus($txn, $payment, $mapped, 'mixx');

        $cleanSuccess = $txn->status === 'SUCCESS' && ! $txn->exception_type;
        Audit::log(
            $cleanSuccess ? 'mixx.collect.success' : 'mixx.collect.result',
            $txn,
            ['reference' => $internalRef, 'status' => $txn->status, 'error' => $mapped['code'], 'phone' => $msisdn],
            $cleanSuccess ? 'SUCCESS' : 'FAILED'
        );

        return [
            'success' => $cleanSuccess,
            'transaction' => $txn,
            'error' => $cleanSuccess ? null : ($parsed['errordescription'] ?? $mapped['description']),
            'http_status' => $cleanSuccess ? 200 : 422,
        ];
    }

    /**
     * Verify an UNKNOWN / TIMEOUT / HOLD transaction (pending checker,
     * manual retry, or status page). Never blind-retries the charge:
     * it queries first and applies whatever the provider confirms.
     */
    public function verify(ProviderTransaction $txn): array
    {
        if ($txn->isTerminal() && ! $txn->exception_type) {
            return ['success' => true, 'status' => $txn->status];
        }

        $path = $this->config->statusPath();
        if (! $path) {
            $txn->update(['exception_type' => 'VERIFY_UNSUPPORTED']);

            return ['success' => false, 'status' => $txn->status, 'error' => 'No status-query path configured for Mixx.'];
        }

        $attemptNo = (int) ($txn->payment?->attempts()->max('attempt_number') ?? 0) + 1;
        $started = now();
        $txn->payment?->recordAttempt('mixx', 'w2a.query', false, null, [
            'attempt_number' => $attemptNo, 'request_id' => $txn->internal_reference, 'status' => 'SENDING', 'started_at' => $started,
        ]);

        $send = $this->client->postXml(
            str_replace('{reference}', urlencode($txn->internal_reference ?? $txn->reference), $path),
            MixxRequestBuilder::toXml(['TYPE' => 'STATUS_QUERY_REQUEST', 'TXNID' => $txn->internal_reference ?? $txn->reference]),
            'w2a.query', $txn->internal_reference, $attemptNo
        );

        if (! $send['transport_ok']) {
            $this->finishAttempt($txn->payment, $attemptNo, $txn->internal_reference, false, 'UNKNOWN', 'error111', $send['error'], $started);

            return ['success' => false, 'status' => $txn->status, 'error' => $send['error']];
        }

        $parsed = MixxResponseParser::billpay($send['body']);
        $mapped = MixxErrorMapper::map('mixx', $parsed['errorcode'] ?? $parsed['result'] ?? '');
        $this->finishAttempt($txn->payment, $attemptNo, $txn->internal_reference, $mapped['internal_status'] === 'SUCCESS', $mapped['internal_status'], $mapped['code'], $parsed['errordescription'] ?? null, $started);

        if (! empty($parsed['refid'])) {
            $txn->update([
                'provider_refid_encrypted' => $parsed['refid'],
                'provider_refid_hash' => Crypto::blindIndex('webhook', 'mixx:ref:'.$parsed['refid']),
            ]);
        }

        $txn = $this->applyMappedStatus($txn, $txn->payment, $mapped, 'mixx-verify');
        $txn->refresh();

        return ['success' => $txn->status === 'SUCCESS', 'status' => $txn->status];
    }

    /**
     * A2W disbursement: partner account -> subscriber wallet.
     * The PIN lives only in this call frame: it is sent, then scrubbed
     * from every persisted/logged representation.
     *
     * @param  array{msisdn:string,amount:mixed,pin:string,sender_name?:string,brand_id?:string,language?:string,reference?:string}  $input
     */
    public function disburse(array $input, $actor = null): array
    {
        try {
            $msisdn = MixxValidator::msisdn($input['msisdn'] ?? '');
            $amount = MixxValidator::amount($input['amount'] ?? 0, $this->config);
        } catch (InvalidArgumentException $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'http_status' => 422];
        }

        $pin = (string) ($input['pin'] ?? '');
        if ($pin === '') {
            return ['success' => false, 'error' => 'Disbursement PIN is required.', 'http_status' => 422];
        }

        if (! $this->config->baseUrl() || ! $this->config->disbursePath()) {
            return ['success' => false, 'error' => 'Mixx disbursement endpoint is not configured yet.', 'http_status' => 503];
        }

        $reference = trim($input['reference'] ?? '');
        if ($reference === '') {
            $reference = $this->nextFeedtanReference('FTD');
        }
        if (Disbursement::where('reference', $reference)->exists()) {
            return ['success' => false, 'error' => 'A disbursement with this reference already exists (idempotency).', 'http_status' => 409];
        }

        $sender = trim($input['sender_name'] ?? 'FEEDTAN PAY');

        $dis = Disbursement::create([
            'provider' => 'mixx',
            'reference' => $reference,
            'internal_reference' => $this->nextFeedtanReference('FTX'),
            'recipient_encrypted' => $msisdn,
            'recipient_hash' => Crypto::blindIndex('customer', $msisdn),
            'recipient_last4' => MixxValidator::last4($msisdn),
            'amount' => $amount,
            'currency' => $this->config->currency(),
            'sender_name_encrypted' => $sender,
            'brand_id' => $input['brand_id'] ?? null,
            'language' => $input['language'] ?? 'sw',
            'status' => 'PENDING',
            'initiated_by' => $actor?->id,
        ]);

        $fields = MixxRequestBuilder::disbursement(
            $reference, $msisdn, $pin, (string) $amount, $sender,
            $dis->brand_id, $dis->language
        );
        $pin = null;
        unset($input['pin']);

        $send = $this->client->postXml(
            $this->config->disbursePath(), MixxRequestBuilder::toXml($fields, 'COMMAND'),
            'a2w.submit', $reference, 1
        );

        if (! $send['transport_ok']) {
            $dis->update(['status' => 'UNKNOWN', 'exception_type' => 'TIMEOUT', 'message' => $send['error']]);
            Audit::log('mixx.disburse.timeout', $dis, ['reference' => $reference], 'FAILED', 'warning');

            return ['success' => false, 'disbursement' => $dis->refresh(), 'error' => $send['error'], 'http_status' => 504];
        }

        $parsed = MixxResponseParser::disbursement($send['body']);
        $mapped = MixxErrorMapper::mapTxnStatus($parsed['txnstatus'] ?? null);

        $dis->update([
            'provider_txnid_encrypted' => $parsed['txnid'],
            'provider_txnid_hash' => $parsed['txnid'] ? Crypto::blindIndex('webhook', 'mixx:'.$parsed['txnid']) : null,
            'message' => $parsed['message'],
            'error_code' => $mapped['code'] ?: null,
            'status' => $mapped['internal_status'],
            'completed_at' => in_array($mapped['internal_status'], ['SUCCESS', 'FAILED'], true) ? now() : null,
            'exception_type' => $mapped['requires_manual_review'] ? 'MANUAL_REVIEW' : null,
        ]);

        Audit::log('mixx.disburse.result', $dis, ['reference' => $reference, 'status' => $dis->status]);

        return [
            'success' => $dis->status === 'SUCCESS',
            'disbursement' => $dis->refresh(),
            'error' => $dis->status === 'SUCCESS' ? null : ($parsed['message'] ?? $mapped['description']),
            'http_status' => $dis->status === 'SUCCESS' ? 200 : 422,
        ];
    }

    protected function applyMappedStatus(ProviderTransaction $txn, ?Payment $payment, array $mapped, string $source): ProviderTransaction
    {
        return DB::transaction(function () use ($txn, $payment, $mapped, $source) {
            $txn = ProviderTransaction::whereKey($txn->id)->lockForUpdate()->first();
            $status = $mapped['internal_status'];

            if (! \App\Payments\PaymentEngine::mayTransition($txn->status, $status)) {
                return $txn;
            }

            $updates = ['status' => $status];
            if (in_array($status, ['SUCCESS', 'FAILED'], true)) {
                $updates['completed_at'] = $txn->completed_at ?? now();
            }
            if ($mapped['requires_manual_review']) {
                $updates['exception_type'] = 'MANUAL_REVIEW';
            } elseif ($status === 'UNKNOWN' || $status === 'HOLD') {
                $updates['exception_type'] = $updates['exception_type'] ?? $txn->exception_type ?? $status;
            }
            if ($status === 'SUCCESS' && ! $txn->exception_type && $txn->settlement_status === 'pending') {
                $updates['settlement_status'] = 'eligible';
            }
            $txn->update($updates);

            if ($payment) {
                $map = ['SUCCESS' => 'completed', 'FAILED' => 'failed', 'REVERSED' => 'reversed', 'PROCESSING' => 'pending', 'PENDING' => 'pending', 'UNKNOWN' => 'pending', 'HOLD' => 'pending'];
                $to = $map[$status] ?? 'pending';
                if ($payment->status !== $to) {
                    $payment->recordHistory($payment->status, $to, $source);
                    $payment->update(['status' => $to, 'last_verified_at' => now(),
                        'completed_at' => $to === 'completed' ? ($payment->completed_at ?? now()) : $payment->completed_at]);
                }
            }

            return $txn;
        });
    }

    protected function finishAttempt(?Payment $payment, int $attemptNo, ?string $requestId, bool $success, string $status, ?string $errorCode, ?string $error, $started): void
    {
        if (! $payment) {
            return;
        }
        $now = now();
        $payment->attempts()->where('attempt_number', $attemptNo)->where('request_id', $requestId)->update([
            'success' => $success,
            'status' => $status,
            'error_code' => $errorCode,
            'error' => $error ? mb_substr($error, 0, 500) : null,
            'completed_at' => $now,
            'duration_ms' => $started ? max(0, $now->diffInMilliseconds($started)) : null,
        ]);
    }

    /**
     * Unique FEEDTAN reference: FTP-YYYYMMDD-000001 (daily sequence).
     */
    public function nextFeedtanReference(string $prefix = 'FTP'): string
    {
        $day = now()->format('Ymd');
        for ($i = 0; $i < 5; $i++) {
            $seq = ProviderTransaction::where('internal_reference', 'like', "{$prefix}-{$day}-%")->count()
                + Disbursement::where('internal_reference', 'like', "{$prefix}-{$day}-%")->count() + 1;
            $ref = sprintf('%s-%s-%06d', $prefix, $day, $seq);
            $taken = ProviderTransaction::where('internal_reference', $ref)->exists()
                || Disbursement::where('internal_reference', $ref)->exists()
                || Payment::where('reference', $ref)->exists();
            if (! $taken) {
                return $ref;
            }
        }

        return sprintf('%s-%s-%06d', $prefix, $day, random_int(1, 999999));
    }
}
