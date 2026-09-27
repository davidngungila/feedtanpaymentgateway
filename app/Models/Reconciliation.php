<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

#[Fillable([
    'agent_id',
    'reconciliation_date',
    'opening_cash',
    'cash_deposits',
    'cash_withdrawals',
    'expected_cash',
    'opening_float',
    'counted_cash',
    'cash_variance',
    'total_float',
    'float_variance',
    'tie_out',
    'network_balances',
    'status',
    'notes',
    'reconciled_by',
])]
class Reconciliation extends Model
{
    protected function casts(): array
    {
        return [
            'reconciliation_date' => 'date',
            'opening_cash' => 'decimal:2',
            'cash_deposits' => 'decimal:2',
            'cash_withdrawals' => 'decimal:2',
            'expected_cash' => 'decimal:2',
            'opening_float' => 'decimal:2',
            'counted_cash' => 'decimal:2',
            'cash_variance' => 'decimal:2',
            'total_float' => 'decimal:2',
            'float_variance' => 'decimal:2',
            'tie_out' => 'decimal:2',
            'network_balances' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Agent, $this>
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reconciler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    /**
     * @return HasMany<ReconciliationCorrection, $this>
     */
    public function corrections(): HasMany
    {
        return $this->hasMany(ReconciliationCorrection::class);
    }

    /**
     * Display code for the session (no dedicated column exists).
     */
    public function getCodeAttribute(): string
    {
        return 'RC-'.str_pad((string) $this->getKey(), 4, '0', STR_PAD_LEFT);
    }

    private ?string $encryptedKeyCache = null;

    /**
     * Use encrypted id in URLs (e.g. /reconciliation/{encrypted}) to hide the raw integer.
     * Deterministic per id so all calls for the same session return the identical string.
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

        if (($det = $this->tryDecryptDeterministic((string) $value)) !== null) {
            return static::find($det);
        }

        try {
            $id = (int) Crypt::decryptString((string) $value);

            return static::find($id);
        } catch (\Throwable) {
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
