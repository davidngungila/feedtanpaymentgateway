<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        'reference', 'gateway', 'provider', 'method', 'channel', 'customer_id', 'customer_name', 'customer_phone', 'customer_email',
        'billing_address', 'customer_country', 'amount', 'fee', 'net_amount', 'currency', 'status', 'gateway_reference',
        'provider_reference', 'provider_transaction_id_encrypted', 'provider_transaction_id_hash',
        'ip_address', 'user_agent', 'notes', 'settlement_status', 'webhook_status', 'settlement_date',
        'operator_id', 'raw_payload', 'webhooks', 'last_verified_at', 'completed_at',
        'description','akiba_type','uwekezaji_type','hisa_type','sms_sent_at','sms_message_id','sms_status','sms_text','collected_amount','channel_provider',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'provider_transaction_id_encrypted' => 'encrypted',
            'raw_payload' => 'array',
            'webhooks' => 'array',
            'settlement_date' => 'datetime',
            'last_verified_at' => 'datetime',
            'completed_at' => 'datetime',
            'sms_sent_at' => 'datetime',
        ];
    }

    protected $hidden = ['provider_transaction_id_encrypted', 'provider_transaction_id_hash'];

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(ProviderTransaction::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(PaymentStatusHistory::class);
    }

    public function recordAttempt(string $provider, string $action, bool $success, ?string $error = null, array $extra = []): void
    {
        try {
            $this->attempts()->create(array_merge([
                'provider' => $provider,
                'action' => $action,
                'success' => $success,
                'error' => $error ? mb_substr($error, 0, 500) : null,
            ], $extra));
        } catch (\Throwable) {
        }
    }

    public function recordHistory(string $from, string $to, string $source = 'system'): void
    {
        try {
            $this->histories()->create([
                'from_status' => $from,
                'to_status' => $to,
                'source' => $source,
                'actor_id' => auth()->id(),
            ]);
        } catch (\Throwable) {
        }
    }

    public function getRouteKey(): string
    {
        $id = (string) $this->getKey();
        $sig = hash_hmac('sha256', $id, (string) config('app.key'));
        $payload = $id . ':' . $sig;
        return rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        if ($field !== null) {
            return parent::resolveRouteBinding($value, $field);
        }
        $decrypted = decrypt_id($value);
        if ($decrypted !== null) {
            return static::find($decrypted);
        }
        if (is_numeric($value)) {
            return static::find((int) $value);
        }
        return null;
    }
}
