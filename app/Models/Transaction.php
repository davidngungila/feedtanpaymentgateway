<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

#[Fillable([
    'daily_opening_id',
    'reference',
    'agent_id',
    'network_id',
    'type',
    'customer_name',
    'customer_phone',
    'amount',
    'fee',
    'commission',
    'status',
    'provider_reference',
    'performed_by',
    'reversed_by',
    'reversed_at',
    'reversal_reason',
    'notes',
    'running_cash_balance',
    'running_float_balance',
    'is_unusual',
    'unusual_reason',
])]
class Transaction extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
            'commission' => 'decimal:2',
            'running_cash_balance' => 'decimal:2',
            'running_float_balance' => 'decimal:2',
            'reversed_at' => 'datetime',
            'is_unusual' => 'boolean',
        ];
    }

    public function markUnusual(?string $reason): void
    {
        $this->update([
            'is_unusual' => filled($reason),
            'unusual_reason' => $reason,
        ]);
    }

    /**
     * @return BelongsTo<DailyOpening, $this>
     */
    public function dailyOpening(): BelongsTo
    {
        return $this->belongsTo(DailyOpening::class);
    }

    /**
     * @return BelongsTo<Agent, $this>
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * @return BelongsTo<Network, $this>
     */
    public function network(): BelongsTo
    {
        return $this->belongsTo(Network::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reverser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    /**
     * @return HasMany<SmsMessage, $this>
     */
    public function smsMessages(): HasMany
    {
        return $this->hasMany(SmsMessage::class);
    }

    private ?string $encryptedKeyCache = null;

    /**
     * Use encrypted id in URLs (e.g. /transactions/{encrypted}/receipt) to hide raw integer.
     * Deterministic per id so all calls for same transaction in same and future requests return identical string (avoids confusion from random IV).
     * Uses HMAC-signed base64 for determinism while still hiding the raw integer.
     */
    public function getRouteKey(): string
    {
        if ($this->encryptedKeyCache !== null) {
            return $this->encryptedKeyCache;
        }

        if (! $this->exists || $this->getKey() === null) {
            return (string) $this->getKey();
        }

        $id = (string) $this->getKey();
        $sig = hash_hmac('sha256', $id, (string) config('app.key'));
        $payload = $id.':'.$sig;

        return $this->encryptedKeyCache = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    }

    private function tryDecryptDeterministic(string $value): ?int
    {
        $padded = strtr($value, '-_', '+/');
        $padLen = strlen($padded) % 4;
        if ($padLen) {
            $padded .= str_repeat('=', 4 - $padLen);
        }

        $decoded = base64_decode($padded, true);
        if ($decoded === false || ! str_contains($decoded, ':')) {
            return null;
        }

        [$id, $sig] = explode(':', $decoded, 2);
        $expected = hash_hmac('sha256', $id, (string) config('app.key'));

        return hash_equals($expected, $sig) && is_numeric($id) ? (int) $id : null;
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        if ($field !== null) {
            return parent::resolveRouteBinding($value, $field);
        }

        // New deterministic format first (fast, no exception)
        if (($det = $this->tryDecryptDeterministic((string) $value)) !== null) {
            return static::find($det);
        }

        // Legacy random-IV Crypt string (from older links)
        try {
            $id = (int) Crypt::decryptString((string) $value);

            return static::find($id);
        } catch (\Throwable) {
            // Fallback to plain id for backward compatibility (e.g. /transactions/41/receipt)
            if (is_numeric($value)) {
                return static::find((int) $value);
            }

            return null;
        }
    }

    public function getEncryptedIdAttribute(): string
    {
        return $this->getRouteKey();
    }
}
