<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'action',
    'entity_type',
    'entity_id',
    'details',
    'ip_address',
    'result',
    'severity',
])]
class SecurityEvent extends Model
{
    /**
     * Append-only: created_at is set by the DB, never updated, and there
     * is no edit/delete UI anywhere in the application.
     */
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
