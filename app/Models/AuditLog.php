<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Models\Concerns\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'action',
    'entity_type',
    'entity_id',
    'details',
    'old_values',
    'new_values',
    'ip_address',
    'user_agent',
    'result',
])]
class AuditLog extends Model
{
    use HasEncryptedRouteKey;
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
