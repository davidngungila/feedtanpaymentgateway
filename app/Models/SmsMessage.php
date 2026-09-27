<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

#[Fillable([
    'device_id',
    'device_line_id',
    'sim_slot',
    'agent_id',
    'network_id',
    'transaction_id',
    'sender',
    'provider',
    'message_body',
    'received_at',
    'sms_hash',
    'transaction_reference',
    'amount',
    'transaction_type',
    'customer_phone',
    'customer_name',
    'balance',
    'processing_status',
    'processing_error',
    'is_duplicate',
    'server_received_at',
])]
class SmsMessage extends Model
{
    protected function casts(): array
    {
        return [
            'sim_slot' => 'integer',
            'amount' => 'decimal:2',
            'balance' => 'decimal:2',
            'received_at' => 'datetime',
            'server_received_at' => 'datetime',
            'is_duplicate' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Device, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * @return BelongsTo<DeviceLine, $this>
     */
    public function deviceLine(): BelongsTo
    {
        return $this->belongsTo(DeviceLine::class);
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
     * @return BelongsTo<Transaction, $this>
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    private ?string $encryptedKeyCache = null;

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
