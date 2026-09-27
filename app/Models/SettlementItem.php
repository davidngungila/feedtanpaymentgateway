<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SettlementItem extends Model
{
    protected $fillable = ['settlement_id', 'provider_transaction_id', 'amount', 'fee', 'net_amount', 'status'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'fee' => 'decimal:2', 'net_amount' => 'decimal:2'];
    }

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(Settlement::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(ProviderTransaction::class, 'provider_transaction_id');
    }
}
