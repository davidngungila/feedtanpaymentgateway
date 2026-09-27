<?php

namespace App\Models;

use App\Models\Concerns\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Disbursement extends Model
{
    use HasEncryptedRouteKey;
    protected $fillable = ['provider', 'reference', 'internal_reference', 'recipient_encrypted', 'recipient_hash', 'recipient_last4', 'amount', 'currency', 'sender_name_encrypted', 'brand_id', 'language', 'status', 'provider_txnid_encrypted', 'provider_txnid_hash', 'provider_refid_encrypted', 'provider_refid_hash', 'message', 'error_code', 'exception_type', 'initiated_by', 'completed_at'];

    protected function casts(): array
    {
        return [
            'recipient_encrypted' => 'encrypted',
            'sender_name_encrypted' => 'encrypted',
            'provider_txnid_encrypted' => 'encrypted',
            'provider_refid_encrypted' => 'encrypted',
            'amount' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    protected $hidden = ['recipient_encrypted', 'recipient_hash', 'sender_name_encrypted', 'provider_txnid_encrypted', 'provider_txnid_hash', 'provider_refid_encrypted', 'provider_refid_hash'];

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, ['SUCCESS', 'FAILED'], true);
    }

    public function maskedRecipient(): string
    {
        return $this->recipient_last4 ? '255******'.$this->recipient_last4 : '••••••••';
    }
}
