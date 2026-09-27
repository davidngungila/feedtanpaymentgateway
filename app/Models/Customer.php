<?php

namespace App\Models;

use App\Models\Concerns\HasEncryptedRouteKey;
use App\Support\Security\Crypto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasEncryptedRouteKey;
    protected $fillable = ['name', 'phone_encrypted', 'phone_hash', 'email_encrypted', 'email_hash', 'national_id_encrypted', 'national_id_hash', 'address_encrypted', 'status'];

    /**
     * Phone/email/ID are encrypted at rest; blind-index hashes keep
     * them searchable without ever storing plaintext.
     */
    protected function casts(): array
    {
        return [
            'phone_encrypted' => 'encrypted',
            'email_encrypted' => 'encrypted',
            'national_id_encrypted' => 'encrypted',
            'address_encrypted' => 'encrypted',
        ];
    }

    protected $hidden = ['phone_encrypted', 'email_encrypted', 'national_id_encrypted', 'address_encrypted', 'phone_hash', 'email_hash', 'national_id_hash'];

    public function transactions(): HasMany
    {
        return $this->hasMany(ProviderTransaction::class);
    }

    public function paymentRequests(): HasMany
    {
        return $this->hasMany(PaymentRequest::class);
    }

    public function maskedPhone(): string
    {
        try {
            return Crypto::maskPhone($this->phone_encrypted);
        } catch (\Throwable) {
            return '••••••••';
        }
    }

    /**
     * Decrypt the phone for an authorized reveal. Every call MUST be
     * audited by the caller (see CustomerController@reveal).
     */
    public function revealPhone(): ?string
    {
        try {
            return $this->phone_encrypted;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function findByPhone(string $phone): ?self
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);

        return static::where('phone_hash', Crypto::blindIndex('customer', $digits))->first();
    }

    public static function findOrCreateFromPayment(string $name, string $phone, ?string $email = null): self
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        $existing = static::findByPhone($digits);
        if ($existing) {
            return $existing;
        }

        return static::create([
            'name' => $name,
            'phone_encrypted' => $digits,
            'phone_hash' => Crypto::blindIndex('customer', $digits),
            'email_encrypted' => $email,
            'email_hash' => Crypto::blindIndex('customer', $email),
        ]);
    }
}
