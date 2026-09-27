<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ProviderTransaction;
use App\Models\WebhookEvent;
use App\Payments\PaymentEngine;
use App\Payments\ProviderRegistry;
use App\Support\Security\Audit;
use App\Support\Security\Crypto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    /**
     * Inbound provider callback.
     *
     * Provider -> HTTPS -> validate signature -> validate timestamp ->
     * replay check -> store raw event encrypted -> idempotency ->
     * apply unified status -> 200.
     */
    public function receive(Request $request, string $provider, PaymentEngine $engine)
    {
        $adapter = ProviderRegistry::get($provider);

        $check = $adapter->verifyWebhookSignature($request);
        if (! ($check['valid'] ?? false)) {
            $this->storeEvent($provider, $check['event_id'] ?? null, $request->all(), 'invalid', 'rejected', $check['error'] ?? 'rejected');
            Audit::log('webhook.rejected', 'webhook', ['provider' => $provider, 'reason' => $check['error'] ?? 'invalid'], 'FAILED', 'warning');

            return response()->json(['success' => false, 'message' => $check['error'] ?? 'Invalid signature.'], $check['http_status'] ?? 401);
        }

        $eventIdHash = Crypto::blindIndex('webhook', $provider.':'.($check['event_id'] ?? Crypto::payloadHash($request->all())));

        // Replay protection: same provider+event twice = acknowledge, don't reprocess.
        $existing = WebhookEvent::where('provider', $provider)->where('event_id_hash', $eventIdHash)->first();
        if ($existing) {
            return response()->json(['success' => true, 'duplicate' => true]);
        }

        $event = $this->storeEvent($provider, $check['event_id'] ?? null, $request->all(), 'verified', 'received', null, $eventIdHash);

        try {
            $e = $adapter->extractWebhookEvent($request);

            $txn = null;
            if (! empty($e['provider_transaction_id'])) {
                $txn = ProviderTransaction::where('provider', $provider)
                    ->where('provider_transaction_id_hash', Crypto::blindIndex('webhook', $provider.':'.$e['provider_transaction_id']))
                    ->first();
            }
            if (! $txn && ! empty($e['order_reference'])) {
                $txn = ProviderTransaction::where('provider', $provider)->where('reference', $e['order_reference'])->first();
            }

            if (! $txn) {
                // Provider-side money with no local record -> unmatched (reconciliation inbox).
                $txn = DB::transaction(function () use ($provider, $e, $event) {
                    $customer = null;
                    if (! empty($e['phone'])) {
                        $digits = PaymentEngine::normalizePhone($e['phone']);
                        if ($digits) {
                            $customer = Customer::findOrCreateFromPayment('Mwanachama', $digits);
                        }
                    }

                    return ProviderTransaction::create([
                        'txn_id' => 'TXN-'.strtoupper(\Illuminate\Support\Str::random(10)),
                        'provider' => $provider,
                        'provider_transaction_id_encrypted' => $e['provider_transaction_id'],
                        'provider_transaction_id_hash' => $e['provider_transaction_id'] ? Crypto::blindIndex('webhook', $provider.':'.$e['provider_transaction_id']) : null,
                        'customer_id' => $customer?->id,
                        'payment_id' => null,
                        'phone_hash' => $customer ? Crypto::blindIndex('customer', $customer->revealPhone()) : null,
                        'amount' => (float) ($e['amount'] ?? 0),
                        'currency' => config('mobile_money.currency', 'TZS'),
                        'reference' => $e['order_reference'],
                        'provider_fee' => 0,
                        'net_amount' => (float) ($e['amount'] ?? 0),
                        'status' => 'PENDING',
                        'initiated_at' => now(),
                        'webhook_status' => 'received',
                        'settlement_status' => 'unmatched',
                        'payload_hash' => Crypto::payloadHash($e['raw'] ?? []),
                    ]);
                });
            }

            $engine->applyStatus($txn, $e['status'] ?? 'PENDING', 'webhook', [
                'webhook' => true,
                'amount' => $e['amount'] ?? null,
                'provider_reference' => $e['provider_transaction_id'] ?? null,
            ]);

            // A SUCCESS transaction that still has no linked payment stays visible as unmatched.
            $txn->refresh();
            if ($txn->status === 'SUCCESS' && ! $txn->payment_id) {
                $txn->update(['settlement_status' => 'unmatched']);
            }

            $event->update(['processing_status' => 'processed', 'processed_at' => now()]);
            Audit::log('webhook.verified', $txn, ['provider' => $provider, 'status' => $txn->status, 'phone' => $e['phone'] ?? null]);

            return response()->json(['success' => true, 'status' => $txn->status]);
        } catch (\Throwable $ex) {
            report($ex);
            $event->update(['processing_status' => 'failed', 'processed_at' => now(), 'error' => mb_substr($ex->getMessage(), 0, 500)]);

            return response()->json(['success' => false, 'message' => 'Webhook processing failed.'], 500);
        }
    }

    protected function storeEvent(string $provider, ?string $eventId, array $payload, string $signature, string $status, ?string $error, ?string $eventIdHash = null): WebhookEvent
    {
        return WebhookEvent::create([
            'provider' => $provider,
            'event_id' => $eventId,
            'event_id_hash' => $eventIdHash ?? Crypto::blindIndex('webhook', $provider.':'.($eventId ?? Crypto::payloadHash($payload))),
            'signature_status' => $signature,
            'payload_encrypted' => $payload,
            'payload_hash' => Crypto::payloadHash($payload),
            'received_at' => now(),
            'processing_status' => $status,
            'error' => $error,
        ]);
    }
}
