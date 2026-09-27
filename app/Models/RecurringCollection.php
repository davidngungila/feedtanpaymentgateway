<?php

namespace App\Models;

use App\Models\Concerns\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringCollection extends Model
{
    use HasEncryptedRouteKey;
    protected $fillable = ['reference', 'customer_id', 'provider', 'amount', 'currency', 'frequency', 'next_run_at', 'last_run_at', 'status'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'next_run_at' => 'datetime', 'last_run_at' => 'datetime'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
