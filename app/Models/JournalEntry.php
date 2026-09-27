<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

#[Fillable([
    'entry_date',
    'reference',
    'description',
    'status',
    'created_by',
    'posted_by',
    'posted_at',
])]
class JournalEntry extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_POSTED = 'posted';

    public const STATUS_REVERSED = 'reversed';

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'posted_at' => 'datetime',
        ];
    }

    public function getRouteKey(): string
    {
        return Crypt::encryptString((string) $this->getKey());
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        try {
            $id = (int) Crypt::decryptString((string) $value);
        } catch (\Throwable) {
            // Fallback: support plain numeric id for backward compatibility (e.g. /finance/journal-entries/1)
            if (is_numeric($value)) {
                return static::find((int) $value);
            }

            return null;
        }

        return static::find($id);
    }

    /**
     * @return HasMany<JournalEntryLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /**
     * Sum of debits across all lines.
     */
    public function totalDebits(): float
    {
        return (float) $this->lines()->sum('debit');
    }

    /**
     * Sum of credits across all lines.
     */
    public function totalCredits(): float
    {
        return (float) $this->lines()->sum('credit');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePosted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_POSTED);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('entry_date')->orderByDesc('id');
    }
}
