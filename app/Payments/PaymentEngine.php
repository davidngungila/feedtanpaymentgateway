<?php

namespace App\Payments;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\ProviderTransaction;
use App\Payments\Providers\ProviderAdapterInterface;
use App\Support\Security\Audit;
use App\Support\Security\Crypto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The single payment engine all five providers flow through:
 *
 *   request -> detect/select provider -> unified transaction record
 *   -> adapter->initiate() -> poll/webhook status -> ledger fields
 *   -> reconciliation -> settlement
 *
 * Status may only move forward (PENDING -> PROCESSING -> SUCCESS/FAILED,
 * or -> REVERSED via a reversal record). A SUCCESS record is never edited;
 * corrections happen through refunds/reversals with their own audit trail.
 */
class PaymentEngine
{
    /**
     * @param  array{provider?:string,phone:string,amount:float|int|string,customer_name?:string,description?:string,currency?:string,reference?:string}  $input
     * @return array{success:bool,payment?:Payment,transaction?:ProviderTransaction,error?:string,http_status:int}
     */
    public function initiate(array $input, $actor = null): array
    {
        $phone = self::normalizePhone($input['phone'] ?? '');
        if (! $phone) {
            return $this->fail('Namba ya simu si sahihi. Mfano: 255712345678', 422);
        }

        $amount = (float) ($input['amount'] ?? 0);
        $min = (float) config('mobile_money.min_amount', 500);
        $max = (float) config('mobile_money.max_amount', 5000000);
        if ($amount < $min || $amount > $max) {
            return $this->fail('Kiasi lazima kiwe kati ya TZS '.number_format($min, 0).' na TZS '.number_format($max, 0).'.', 422);
        }

        $code = strtolower(trim($input['provider'] ?? 'auto'));
        if ($code === '' || $code === 'auto') {
            $code = ProviderRegistry::detect($phone);
            if (! $code) {
                return $this->fail('Mtandao wa simu hautambuliki kutoka kwenye namba. Chagua mtandao: M-Pesa, Airtel Money, Mixx by Yas, HaloPesa au T-Pesa.', 422);
            }
        }

        try {
            $adapter = ProviderRegistry::get($code);
        } catch (\InvalidArgumentException $e) {
            return $this->fail($e->getMessage(), 422);
        }

        $reference = $input['reference'] ?? $this->newReference();
        if (Payment::where('reference', $reference)->exists()) {
            return $this->fail('Tayari kuna malipo yenye reference hii (idempotency).', 409);
        }

        $fee = $this->assessFee($code, $amount);

        try {
            $result = DB::transaction(function () use ($input, $actor, $phone, $amount, $code, $adapter, $reference, $fee) {
                $customer = Customer::findOrCreateFromPayment(
                    $input['customer_name'] ?? 'Mwanachama',
                    $phone,
                    $input['customer_email'] ?? null
                );

                $payment = Payment::create([
                    'reference' => $reference,
                    'gateway' => 'direct',
                    'provider' => $code,
                    'method' => 'mobile_money',
                    'channel' => 'USSD',
                    'customer_id' => $customer->id,
                    'customer_name' => $input['customer_name'] ?? 'Mwanachama',
                    'customer_phone' => $phone,
                    'customer_country' => 'TZ',
                    'amount' => $amount,
                    'fee' => $fee,
                    'net_amount' => round($amount - $fee, 2),
                    'currency' => $input['currency'] ?? config('mobile_money.currency', 'TZS'),
                    'status' => 'pending',
                    'notes' => $input['description'] ?? null,
                    'description' => $input['description'] ?? null,
                    'settlement_status' => 'pending',
                    'webhook_status' => 'pending',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'operator_id' => $actor?->id,
                ]);

                $txn = ProviderTransaction::create([
                    'txn_id' => 'TXN-'.strtoupper(Str::random(10)),
                    'provider' => $code,
                    'customer_id' => $customer->id,
                    'payment_id' => $payment->id,
                    'phone_hash' => Crypto::blindIndex('customer', $phone),
                    'amount' => $amount,
                    'currency' => $payment->currency,
                    'reference' => $reference,
                    'provider_fee' => $fee,
                    'net_amount' => round($amount - $fee, 2),
                    'status' => 'PENDING',
                    'initiated_at' => now(),
                    'webhook_status' => 'pending',
                    'settlement_status' => 'pending',
                ]);

                $init = $adapter->initiate([
                    'amount' => $amount,
                    'currency' => $payment->currency,
                    'order_reference' => $reference,
                    'phone' => $phone,
                    'customer_name' => $payment->customer_name,
                    'description' => $payment->description,
                ]);

                $payment->recordAttempt($code, 'initiate', $init['success'] ?? false, $init['error'] ?? null);

                if (! ($init['success'] ?? false)) {
                    $payment->update(['status' => 'failed']);
                    $txn->update(['status' => 'FAILED', 'completed_at' => now()]);
                    $payment->recordHistory('pending', 'failed', 'initiate');
                } else {
                    $providerRef = $init['provider_reference'] ?? null;
                    $payment->update([
                        'gateway_reference' => $providerRef ?? $reference,
                        'provider_reference' => $providerRef,
                        'provider_transaction_id_encrypted' => $providerRef,
                        'provider_transaction_id_hash' => Crypto::blindIndex('webhook', $providerRef ? $code.':'.$providerRef : $code.':'.$reference),
                        'raw_payload' => array_merge($payment->raw_payload ?? [], ['initiate' => $init['raw'] ?? null]),
                    ]);
                    $txn->update([
                        'provider_transaction_id_encrypted' => $providerRef ?? $reference,
                        'provider_transaction_id_hash' => Crypto::blindIndex('webhook', $providerRef ? $code.':'.$providerRef : $code.':'.$reference),
                        'payload_hash' => Crypto::payloadHash($init['raw'] ?? []),
                    ]);
                }

                return [$payment, $txn, $init];
            });
        } catch (\Throwable $e) {
            report($e);

            return $this->fail('Imeshindikwa kuanzisha malipo: '.$e->getMessage(), 500);
        }

        [$payment, $txn, $init] = $result;

        Audit::log(
            'payment.created',
            $payment,
            ['provider' => $code, 'reference' => $reference, 'amount' => $amount, 'phone' => $phone, 'success' => (bool) ($init['success'] ?? false)],
            ($init['success'] ?? false) ? 'SUCCESS' : 'FAILED'
        );

        if (! ($init['success'] ?? false)) {
            return ['success' => false, 'payment' => $payment, 'transaction' => $txn, 'error' => $init['error'] ?? 'Imeshindikwa kutuma malipo.', 'http_status' => $init['http_status'] ?? 422];
        }

        return ['success' => true, 'payment' => $payment, 'transaction' => $txn, 'http_status' => 200];
    }

    /**
     * Poll the provider and apply the latest status (forward-only).
     */
    public function refresh(Payment $payment): array
    {
        $txn = ProviderTransaction::where('payment_id', $payment->id)->latest()->first();
        $adapter = ProviderRegistry::get($payment->provider ?: 'mpesa');
        $ref = $payment->provider_reference ?: $payment->reference;

        $result = $adapter->query($ref);
        $payment->recordAttempt($payment->provider, 'query', (bool) ($result['success'] ?? false), $result['error'] ?? null);

        if (! ($result['success'] ?? false)) {
            return ['success' => false, 'error' => $result['error'] ?? 'Status query failed.', 'http_status' => $result['http_status'] ?? 0];
        }

        if ($txn) {
            $this->applyStatus($txn, $result['status'], 'poll', [
                'amount' => $result['amount'] ?? null,
                'provider_reference' => $result['provider_reference'] ?? null,
            ]);
            $txn->refresh();
        }

        $payment->update(['last_verified_at' => now()]);

        return ['success' => true, 'status' => $txn?->status, 'http_status' => 200];
    }

    /**
     * Apply a canonical status to a transaction + linked payment.
     * Forward-only: terminal states (SUCCESS/FAILED/REVERSED) win and a
     * SUCCESS record is never modified except through refunds/reversals.
     */
    public function applyStatus(ProviderTransaction $txn, string $status, string $source, array $context = []): ProviderTransaction
    {
        return DB::transaction(function () use ($txn, $status, $source, $context) {
            $txn = ProviderTransaction::whereKey($txn->id)->lockForUpdate()->first();
            $from = $txn->status;

            if (! self::mayTransition($from, $status)) {
                return $txn;
            }

            $updates = ['status' => $status];
            if (in_array($status, ['SUCCESS', 'FAILED', 'REVERSED'], true)) {
                $updates['completed_at'] = $txn->completed_at ?? now();
            }
            if (! empty($context['provider_reference'])) {
                $ref = (string) $context['provider_reference'];
                $updates['provider_transaction_id_encrypted'] = $ref;
                $updates['provider_transaction_id_hash'] = Crypto::blindIndex('webhook', $txn->provider.':'.$ref);
            }
            if (array_key_exists('amount', $context) && $context['amount'] !== null && $txn->amount == 0) {
                $updates['amount'] = (float) $context['amount'];
                $updates['net_amount'] = round((float) $context['amount'] - (float) $txn->provider_fee, 2);
            }
            if (($context['webhook'] ?? false) === true) {
                $updates['webhook_status'] = 'received';
            }
            if ($status === 'SUCCESS' && $txn->settlement_status === 'pending') {
                $updates['settlement_status'] = 'eligible';
            }

            $txn->update($updates);

            if ($txn->payment) {
                $payment = $txn->payment;
                $map = ['SUCCESS' => 'completed', 'FAILED' => 'failed', 'REVERSED' => 'reversed', 'PROCESSING' => 'pending', 'PENDING' => 'pending'];
                $to = $map[$status] ?? 'pending';
                if ($payment->status !== $to) {
                    $payment->recordHistory($payment->status, $to, $source);
                    $payment->update([
                        'status' => $to,
                        'completed_at' => $to === 'completed' ? ($payment->completed_at ?? now()) : $payment->completed_at,
                        'settlement_status' => $to === 'completed' ? 'eligible' : $payment->settlement_status,
                        'last_verified_at' => now(),
                    ]);
                }
            }

            return $txn;
        });
    }

    public static function mayTransition(string $from, string $to): bool
    {
        if ($from === $to) {
            return false;
        }
        // Terminal states never move (corrections go via refunds/reversals).
        if (in_array($from, ['SUCCESS', 'FAILED', 'REVERSED'], true)) {
            return false;
        }

        return true;
    }

    /**
     * Normalize a TZ mobile number to 255XXXXXXXXX. Returns '' if invalid.
     */
    public static function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', (string) $phone);
        if (str_starts_with($digits, '255') && strlen($digits) === 12) {
            return $digits;
        }
        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            return '255'.substr($digits, 1);
        }
        if (strlen($digits) === 9) {
            return '255'.$digits;
        }

        return '';
    }

    protected function newReference(): string
    {
        do {
            $ref = 'PAY'.now()->format('YmdHis').random_int(100, 999);
            $ref = substr($ref, 0, 20);
        } while (Payment::where('reference', $ref)->exists());

        return $ref;
    }

    /**
     * Provider fee assessment (configurable per provider, defaults 0).
     */
    protected function assessFee(string $provider, float $amount): float
    {
        $rule = config("mobile_money.providers.{$provider}.fee", []);
        $percent = (float) ($rule['percent'] ?? 0);
        $flat = (float) ($rule['flat'] ?? 0);

        return round($amount * $percent / 100 + $flat, 2);
    }

    protected function fail(string $error, int $httpStatus): array
    {
        return ['success' => false, 'error' => $error, 'http_status' => $httpStatus];
    }

    public function adapterFor(Payment $payment): ProviderAdapterInterface
    {
        return ProviderRegistry::get($payment->provider ?: 'mpesa');
    }
}
