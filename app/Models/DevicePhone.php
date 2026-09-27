<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'device_id',
    'device_uid',
    'model',
    'android_version',
    'app_version',
    'ip',
    'first_seen_at',
    'last_seen_at',
])]
class DevicePhone extends Model
{
    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
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
     * A handset is "connected" when it reported activity within the offline
     * threshold (same window the device card uses to mark a device offline).
     */
    public function isOnline(): bool
    {
        $threshold = now()->subMinutes((int) config('sms.offline_after_minutes', 10));

        return $this->last_seen_at !== null && $this->last_seen_at->gt($threshold);
    }

    /**
     * Record activity (bootstrap/heartbeat) for this handset.
     *
     * @param  array<string, mixed>  $attrs
     */
    public static function markSeen(Device $device, string $deviceUid, array $attrs = []): self
    {
        $phone = static::firstOrCreate(
            ['device_id' => $device->id, 'device_uid' => $deviceUid],
            ['first_seen_at' => now()],
        );

        $phone->forceFill(array_merge([
            'model' => $attrs['model'] ?? $phone->model,
            'android_version' => $attrs['android_version'] ?? $phone->android_version,
            'app_version' => $attrs['app_version'] ?? $phone->app_version,
            'ip' => $attrs['ip'] ?? $phone->ip,
            'last_seen_at' => now(),
        ]))->save();

        return $phone;
    }
}
