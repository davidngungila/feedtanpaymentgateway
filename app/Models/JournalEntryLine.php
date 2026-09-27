<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'journal_entry_id',
    'account_id',
    'description',
    'debit',
    'credit',
])]
class JournalEntryLine extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<JournalEntry, $this>
     */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Signed value in the direction of the account's normal balance.
     */
    public function signedAmount(): float
    {
        if ($this->account === null) {
            return 0.0;
        }

        return $this->account->isDebitNormal()
            ? (float) $this->debit - (float) $this->credit
            : (float) $this->credit - (float) $this->debit;
    }

    /**
     * Raw signed movement (debit minus credit), independent of account type.
     */
    public function rawDelta(): float
    {
        return (float) $this->debit - (float) $this->credit;
    }
}
