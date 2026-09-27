<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderErrorCode extends Model
{
    protected $fillable = ['provider', 'code', 'description', 'internal_status', 'retryable', 'requires_manual_review'];

    protected function casts(): array
    {
        return ['retryable' => 'boolean', 'requires_manual_review' => 'boolean'];
    }
}
