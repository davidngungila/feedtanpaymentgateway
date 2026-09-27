<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAttempt extends Model
{
    public $timestamps = false;

    protected $fillable = ['payment_id', 'provider', 'action', 'attempt_number', 'request_id', 'success', 'status', 'error', 'error_code', 'started_at', 'completed_at', 'duration_ms'];

    protected function casts(): array
    {
        return ['success' => 'boolean', 'created_at' => 'datetime', 'started_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
