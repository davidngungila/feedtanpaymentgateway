<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reversal extends Model
{
    protected $fillable = ['reference', 'payment_id', 'provider', 'amount', 'reason', 'status', 'requested_by', 'approved_by', 'provider_reference_encrypted', 'provider_reference_hash'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'provider_reference_encrypted' => 'encrypted'];
    }

    protected $hidden = ['provider_reference_encrypted', 'provider_reference_hash'];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
