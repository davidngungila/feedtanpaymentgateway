<?php

namespace App\Models;

use App\Models\Concerns\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PaymentLink extends Model
{
    protected $fillable = ['token', 'code', 'payment_request_id', 'status', 'expires_at', 'redeemed_at'];

    protected static function booted(): void
    {
        static::creating(function (self $link) {
            if (empty($link->token)) {
                $link->token = Str::random(40);
            }
            if (empty($link->code)) {
                do {
                    $code = strtoupper(Str::random(6));
                } while (static::where('code', $code)->exists());
                $link->code = $code;
            }
        });
    }

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'redeemed_at' => 'datetime'];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(PaymentRequest::class, 'payment_request_id');
    }
}
