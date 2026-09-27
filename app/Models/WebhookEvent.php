<?php

namespace App\Models;

use App\Models\Concerns\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    use HasEncryptedRouteKey;
    protected $fillable = ['provider', 'event_id', 'event_id_hash', 'signature_status', 'payload_encrypted', 'payload_hash', 'received_at', 'processed_at', 'processing_status', 'error'];

    /**
     * Raw provider payloads are encrypted at rest; only the SHA-256
     * integrity hash stays queryable.
     */
    protected function casts(): array
    {
        return [
            'payload_encrypted' => 'encrypted:array',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    protected $hidden = ['payload_encrypted'];
}
