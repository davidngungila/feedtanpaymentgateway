<?php

namespace App\Models;

use App\Models\Concerns\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasEncryptedRouteKey;
    protected $fillable = ['number', 'customer_id', 'provider', 'amount', 'currency', 'due_at', 'status', 'notes'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'due_at' => 'datetime'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
