<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

#[Fillable([
    'device_uid',
    'device_code',
    'name',
    'model',
    'agent_id',
    'network_id',
    'phone_number',
    'sim_number',
    'android_version',
    'app_version',
    'branch',
    'status',
    'last_ip',
    'last_sms_at',
    'last_sync_at',
    'last_heartbeat_at',
    'activated_at',
    'suspended_at',
    'blocked_at',
    'revoked_at',
])]
class Device extends Model
{
    public const MANAGED_STATUSES = ['pending', 'active', 'suspended', 'blocked', 'revoked'];

    /**
     * Device URLs carry an encrypted id instead of the plain integer or pairing
     * code:
     *
     *   /devices/{encrypted}-token
     *
     * Decrypting happens in resolveRouteBinding() so every device route keeps
     * working while the address bar reveals nothing about the record.
     */
    public function getRouteKey(): string
    {
        return Crypt::encryptString((string) $this->getKey());
    }

    /**
     * @return static|null
     */
    public function resolveRouteBinding($value, $field = null): ?self
    {
        try {
            $id = (int) Crypt::decryptString((string) $value);
        } catch (\Throwable) {
            return null;
        }

        return static::find($id);
    }

    private const DEVICE_CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    protected function casts(): array
    {
        return [
            'last_sms_at' => 'datetime',
            'last_sync_at' => 'datetime',
            'last_heartbeat_at' => 'datetime',
            'activated_at' => 'datetime',
            'suspended_at' => 'datetime',
            'blocked_at' => 'datetime',
            'revoked_at' => 'datetime',
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
     * @return BelongsTo<Network, $this>
     */
    public function network(): BelongsTo
    {
        return $this->belongsTo(Network::class);
    }

    /**
     * All networks this device is allowed to ingest SMS for and whose
     * transactions it can access (many-to-many).
     *
     * @return BelongsToMany<Network, $this>
     */
    public function networks(): BelongsToMany
    {
        return $this->belongsToMany(Network::class)->withTimestamps();
    }

    /**
     * @return HasMany<SmsMessage, $this>
     */
    public function smsMessages(): HasMany
    {
        return $this->hasMany(SmsMessage::class);
    }

    /**
     * Physical SIM lines (chips) installed in the handset. Each line maps a
     * SIM slot to a network and carries its own phone number.
     *
     * @return HasMany<DeviceLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(DeviceLine::class)->orderBy('sim_slot');
    }

    /**
     * Handsets that have paired with this device code (one code can be paired
     * by several phones). Tracked for live connection status.
     *
     * @return HasMany<DevicePhone, $this>
     */
    public function phones(): HasMany
    {
        return $this->hasMany(DevicePhone::class)->orderByDesc('last_seen_at');
    }

    /**
     * Resolve the line a message arrived on, by Android subscription id first,
     * then by the reported SIM slot.
     */
    public function lineFor(?string $subscriptionId, $simSlot): ?DeviceLine
    {
        $line = $this->lines()->get();

        if ($subscriptionId !== null && $subscriptionId !== '') {
            $match = $line->firstWhere('subscription_id', $subscriptionId);

            if ($match !== null) {
                return $match;
            }
        }

        if ($simSlot !== null && $simSlot !== '') {
            return $line->firstWhere('sim_slot', (int) $simSlot);
        }

        return null;
    }

    /**
     * Generate a unique 6-character device code using an unambiguous alphabet
     * (no O/0, I/1, l).
     */
    public static function generateDeviceCode(): string
    {
        $alphabet = self::DEVICE_CODE_ALPHABET;
        $length = strlen($alphabet);

        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $alphabet[random_int(0, $length - 1)];
            }
        } while (static::where('device_code', $code)->exists());

        return $code;
    }

    public function approve(): void
    {
        $this->status = 'active';
        $this->activated_at = now();
        $this->suspended_at = null;
        $this->blocked_at = null;
        $this->revoked_at = null;
        $this->save();
    }

    public function suspend(): void
    {
        $this->status = 'suspended';
        $this->suspended_at = now();
        $this->save();
    }

    public function block(): void
    {
        $this->status = 'blocked';
        $this->blocked_at = now();
        $this->save();
    }

    public function revoke(): void
    {
        $this->status = 'revoked';
        $this->revoked_at = now();
        $this->save();
    }

    /**
     * A device that is active but has not checked in recently is offline.
     */
    public function isOffline(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $threshold = now()->subMinutes((int) config('sms.offline_after_minutes', 10));

        return $this->last_heartbeat_at === null || $this->last_heartbeat_at->lt($threshold);
    }

    /**
     * Display label for the served badge.
     */
    public function displayedStatus(): string
    {
        return $this->isOffline() ? 'offline' : $this->status;
    }

    public function todayCounts(): array
    {
        return [
            'received' => $this->smsMessages()->whereDate('server_received_at', today())->count(),
            'processed' => $this->smsMessages()->whereDate('server_received_at', today())->where('processing_status', 'RECORDED')->count(),
            'pending' => $this->smsMessages()->whereDate('server_received_at', today())->whereIn('processing_status', ['RECEIVED', 'PARSED', 'NEEDS_REVIEW'])->count(),
            'failed' => $this->smsMessages()->whereDate('server_received_at', today())->where('processing_status', 'FAILED')->count(),
            'duplicate' => $this->smsMessages()->whereDate('server_received_at', today())->where(fn ($q) => $q->where('is_duplicate', true)->orWhere('processing_status', 'DUPLICATE'))->count(),
        ];
    }

    /**
     * Networks the device is assigned to via the pivot, falling back to the
     * legacy primary network for devices registered before the pivot existed.
     *
     * @return Collection<int, Network>
     */
    public function assignedNetworks(): Collection
    {
        $networks = $this->networks()->get();

        if ($networks->isEmpty() && $this->network_id !== null) {
            $primary = $this->network()->first();

            if ($primary !== null) {
                $networks->push($primary);
            }
        }

        return $networks;
    }

    /**
     * @return array<int, string>
     */
    public function networkCodes(): array
    {
        return $this->assignedNetworks()->pluck('code')->filter()->values()->all();
    }
}
