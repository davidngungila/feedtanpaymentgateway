<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'reconciliation_id',
    'scope',
    'network_id',
    'type',
    'reference',
    'amount',
    'notes',
    'created_by',
])]
class ReconciliationCorrection extends Model
{
    public const TYPE_ERROR_FUNDS = 'error_funds';

    public const TYPE_CUSTOMER_OVERPAID = 'customer_overpaid';

    public const TYPE_CASH_SHORTAGE = 'cash_shortage';

    public const TYPE_REFUND_GIVEN = 'refund_given';

    public const TYPE_FLOAT_TOPUP = 'float_topup';

    public const TYPE_OTHER = 'other';

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function types(): array
    {
        return [
            self::TYPE_ERROR_FUNDS => 'Money received in error — not refunded',
            self::TYPE_CUSTOMER_OVERPAID => 'Customer overpaid cash',
            self::TYPE_CASH_SHORTAGE => 'Cash shortage / loss',
            self::TYPE_REFUND_GIVEN => 'Refund given to customer',
            self::TYPE_FLOAT_TOPUP => 'Float top-up / adjustment',
            self::TYPE_OTHER => 'Other',
        ];
    }

    public function typeLabel(): string
    {
        return self::types()[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    /**
     * Signed effect this correction has on the session variance.
     *
     * Corrections that add funds back (error funds kept, customer overpaid,
     * float top-up) settle a positive variance; reductions (shortage, refund)
     * settle a negative one.
     */
    public function signedAmount(): float
    {
        $sign = in_array($this->type, [self::TYPE_CASH_SHORTAGE, self::TYPE_REFUND_GIVEN], true) ? -1 : 1;

        return $sign * (float) $this->amount;
    }

    /**
     * @return BelongsTo<Reconciliation, $this>
     */
    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(Reconciliation::class);
    }

    /**
     * @return BelongsTo<Network, $this>
     */
    public function network(): BelongsTo
    {
        return $this->belongsTo(Network::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
