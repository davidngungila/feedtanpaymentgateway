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
    'code',
    'name',
    'type',
    'parent_id',
    'description',
    'is_active',
])]
class Account extends Model
{
    use HasFactory;

    protected $table = 'chart_of_accounts';

    public const TYPE_ASSET = 'asset';

    public const TYPE_LIABILITY = 'liability';

    public const TYPE_EQUITY = 'equity';

    public const TYPE_INCOME = 'income';

    public const TYPE_EXPENSE = 'expense';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<self, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<self, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return HasMany<JournalEntryLine, $this>
     */
    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    /**
     * Debit-normal accounts increase on debit (assets, expenses).
     */
    public function isDebitNormal(): bool
    {
        return in_array($this->type, [self::TYPE_ASSET, self::TYPE_EXPENSE], true);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
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
            if (is_numeric($value)) {
                return static::find((int) $value);
            }

            return null;
        }

        return static::find($id);
    }
}
