<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Hidden(['client_secret', 'api_key', 'webhook_secret', 'meta'])]
class ProviderCredential extends Model
{
    protected $fillable = ['provider_id', 'environment', 'label', 'client_id', 'client_secret', 'api_key', 'webhook_secret', 'meta'];

    /**
     * Secrets are encrypted at rest. They are NEVER serialized
     * (see #[Hidden]) and must never be passed to views or logs.
     */
    protected function casts(): array
    {
        return [
            'client_secret' => 'encrypted',
            'api_key' => 'encrypted',
            'webhook_secret' => 'encrypted',
            'meta' => 'encrypted:array',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
