<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable([
    'agent_id',
    'user_id',
    'opening_date',
    'cash_opening',
    'float_openings',
    'notes',
    'is_closed',
    'cash_closing',
    'float_closings',
    'total_volume',
    'total_commission',
    'total_transactions',
    'closed_at',
])]
class DailyOpening extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'opening_date' => 'date',
            'cash_opening' => 'decimal:2',
            'float_openings' => 'array',
            'cash_closing' => 'decimal:2',
            'float_closings' => 'array',
            'total_volume' => 'decimal:2',
            'total_commission' => 'decimal:2',
            'is_closed' => 'boolean',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Agent, $this>
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function getFloatOpening(int $networkId): float
    {
        return (float) ($this->float_openings[$networkId] ?? 0);
    }

    public function getFloatClosing(int $networkId): float
    {
        return (float) ($this->float_closings[$networkId] ?? 0);
    }

    public function totalFloatOpening(): float
    {
        if (! is_array($this->float_openings)) {
            return 0;
        }

        return (float) array_sum($this->float_openings);
    }

    public function totalFloatClosing(): float
    {
        if (! is_array($this->float_closings)) {
            return 0;
        }

        return (float) array_sum($this->float_closings);
    }

    public function addTransactionVolume(float $amount, float $commission): void
    {
        $this->total_volume = (float) $this->total_volume + $amount;
        $this->total_commission = (float) $this->total_commission + $commission;
        $this->total_transactions = (int) $this->total_transactions + 1;
        $this->save();
    }

    public function reverseTransactionVolume(float $amount, float $commission): void
    {
        $this->total_volume = max(0, (float) $this->total_volume - $amount);
        $this->total_commission = max(0, (float) $this->total_commission - $commission);
        $this->total_transactions = max(0, (int) $this->total_transactions - 1);
        $this->save();
    }

    public function scopeForAgentAndDate($query, int $agentId, Carbon $date)
    {
        return $query->where('agent_id', $agentId)
            ->where('opening_date', $date);
    }

    public function scopeOpen($query)
    {
        return $query->where('is_closed', false);
    }

    public function scopeToday($query)
    {
        return $query->where('opening_date', today());
    }

    public function getRouteKeyName(): string
    {
        return 'opening_date';
    }

    public function getRouteKey(): string
    {
        return $this->opening_date instanceof Carbon
            ? $this->opening_date->format('Y-m-d')
            : (string) $this->opening_date;
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if ($field !== null) {
            return parent::resolveRouteBinding($value, $field);
        }

        $date = rescue(
            static function () use ($value): ?Carbon {
                $time = is_string($value) ? $value : '';
                $parsed = Carbon::createFromFormat('Y-m-d', $time);

                return $parsed && $parsed->format('Y-m-d') === $time
                    ? $parsed->startOfDay()
                    : null;
            },
            null,
            false
        );

        if ($date === null) {
            return null;
        }

        $agent = cash_point();
        if ($agent === null) {
            return static::whereDate('opening_date', $date)->first();
        }

        return static::forAgentAndDate($agent->id, $date)->first();
    }
}
