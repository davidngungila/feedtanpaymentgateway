<?php

namespace App\Models;

use App\Models\Concerns\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The unified internal transaction. Every provider — M-Pesa, Airtel
 * Money, Mixx by Yas, HaloPesa, T-Pesa — lands in this same structure:
 * TXN ID, provider, provider transaction ID (encrypted + blind index),
 * customer, phone (hash only), amount, currency, reference, provider fee,
 * net amount, status, initiated/completed at, webhook + settlement state.
 */
class ProviderTransaction extends Model
{
    use HasEncryptedRouteKey;
    protected $fillable = ['txn_id', 'internal_reference', 'provider', 'provider_transaction_id_encrypted', 'provider_transaction_id_hash', 'provider_refid_encrypted', 'provider_refid_hash', 'customer_id', 'payment_id', 'phone_hash', 'msisdn_encrypted', 'phone_last4', 'sender_name_encrypted', 'sender_name_confirmed', 'customer_reference_id', 'amount', 'currency', 'reference', 'provider_fee', 'net_amount', 'status', 'initiated_at', 'completed_at', 'webhook_status', 'settlement_status', 'exception_type', 'expected_amount', 'payload_hash', 'idempotency_key'];

    protected function casts(): array
    {
        return [
            'provider_transaction_id_encrypted' => 'encrypted',
            'provider_refid_encrypted' => 'encrypted',
            'msisdn_encrypted' => 'encrypted',
            'sender_name_encrypted' => 'encrypted',
            'amount' => 'decimal:2',
            'provider_fee' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'initiated_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected $hidden = ['provider_transaction_id_encrypted', 'provider_transaction_id_hash', 'provider_refid_encrypted', 'provider_refid_hash', 'msisdn_encrypted', 'sender_name_encrypted', 'phone_hash', 'idempotency_key'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, ['SUCCESS', 'FAILED', 'REVERSED'], true);
    }

    /**
     * Full payment details live on the payment page; transactions
     * without a linked payment fall back to the transaction view.
     */
    public function detailsUrl(): string
    {
        if ($this->payment) {
            return route('payments.show', $this->payment);
        }

        return route('collections.payments.show', $this);
    }
}
