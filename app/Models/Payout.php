<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payout extends Model
{
    protected $fillable = [
        'reference', 'beneficiary_name', 'beneficiary_account', 'beneficiary_phone', 'bank_name', 'method', 'gateway',
        'gateway_reference', 'status', 'amount', 'fee', 'currency', 'purpose', 'notes', 'source_account',
        'created_by', 'approved_by', 'approved_at', 'dispatched_at', 'failed_at', 'rejected_at',
        'rejection_reason', 'failure_reason', 'approval_notes', 'gateway_status', 'gateway_latency',
        'gateway_payload', 'gateway_response',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
            'approved_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'failed_at' => 'datetime',
            'rejected_at' => 'datetime',
            'gateway_payload' => 'array',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getRouteKey(): string
    {
        $id = (string) $this->getKey();
        $sig = hash_hmac('sha256', $id, (string) config('app.key'));
        $payload = $id . ':' . $sig;
        return rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        if ($field !== null) {
            return parent::resolveRouteBinding($value, $field);
        }
        $decrypted = decrypt_id($value);
        if ($decrypted !== null) {
            return static::find($decrypted);
        }
        if (is_numeric($value)) {
            return static::find((int) $value);
        }
        return null;
    }
}
