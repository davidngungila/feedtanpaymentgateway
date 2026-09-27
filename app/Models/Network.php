<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

#[Fillable([
    'name',
    'code',
    'color',
    'is_active',
])]
class Network extends Model
{
    use HasFactory;

    /**
     * Network URLs carry an encrypted id instead of the plain integer:
     *
     *   /networks/{encrypted}-token
     *
     * Decrypting happens in resolveRouteBinding() so every network route
     * keeps working while the address bar reveals nothing about the record.
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

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return Builder<$this>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @return Builder<$this>
     */
    public function scopeSuspended(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    /**
     * @return HasMany<NetworkBalance, $this>
     */
    public function balances(): HasMany
    {
        return $this->hasMany(NetworkBalance::class);
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @return BelongsToMany<Device, $this>
     */
    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(Device::class)->withTimestamps();
    }
}
