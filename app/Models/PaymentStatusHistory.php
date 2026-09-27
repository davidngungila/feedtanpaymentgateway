<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentStatusHistory extends Model
{
    public $timestamps = false;

    protected $fillable = ['payment_id', 'from_status', 'to_status', 'source', 'actor_id'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
