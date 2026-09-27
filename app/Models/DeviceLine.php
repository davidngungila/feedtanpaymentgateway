<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'device_id',
    'sim_slot',
    'network_id',
    'phone_number',
    'subscription_id',
])]
class DeviceLine extends Model
{
    /**
     * @return BelongsTo<Device, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * @return BelongsTo<Network, $this>
     */
    public function network(): BelongsTo
    {
        return $this->belongsTo(Network::class);
    }

    /**
     * Display label, e.g. "SIM 2".
     */
    public function displayName(): string
    {
        return 'SIM '.$this->sim_slot;
    }
}
