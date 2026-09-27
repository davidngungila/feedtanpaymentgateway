<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provider extends Model
{
    protected $fillable = ['code', 'name', 'color', 'driver', 'is_active', 'config'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'config' => 'array'];
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(ProviderCredential::class);
    }

    public function liveCredential(): ?ProviderCredential
    {
        return $this->credentials()->where('environment', 'live')->first();
    }

    public function prefixes(): array
    {
        return (array) ($this->config['prefixes'] ?? config("mobile_money.providers.{$this->code}.prefixes", []));
    }
}
