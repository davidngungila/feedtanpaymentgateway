<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'phone', 'password', 'role', 'agent_id', 'is_active', 'two_factor_secret', 'two_factor_enabled', 'two_factor_recovery_codes', 'profile_photo_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Agent, $this>
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function avatarUrl(): string
    {
        if (! $this->profile_photo_path) {
            return '';
        }

        $path = ltrim($this->profile_photo_path, '/');

        if (! str_starts_with($path, 'avatars/')) {
            return '';
        }

        if (! Storage::disk('public')->exists($path)) {
            return '';
        }

        return route('avatar.show', substr($path, strlen('avatars/')));
    }

    public function hasPermission(string $permission): bool
    {
        return \App\Support\Security\Permissions::check($this, $permission);
    }

    public function isAdmin(): bool
    {
        return ($this->role ?? null) === 'admin';
    }

    private ?string $encryptedKeyCache = null;

    public function getRouteKey(): string
    {
        if ($this->encryptedKeyCache !== null) {
            return $this->encryptedKeyCache;
        }

        if (! $this->exists || $this->getKey() === null) {
            return (string) $this->getKey();
        }

        $id = (string) $this->getKey();
        $sig = hash_hmac('sha256', $id, (string) config('app.key'));
        $payload = $id.':'.$sig;

        return $this->encryptedKeyCache = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    }

    private function tryDecryptDeterministic(string $value): ?int
    {
        $padded = strtr($value, '-_', '+/');
        $padLen = strlen($padded) % 4;
        if ($padLen) {
            $padded .= str_repeat('=', 4 - $padLen);
        }

        $decoded = base64_decode($padded, true);
        if ($decoded === false || ! str_contains($decoded, ':')) {
            return null;
        }

        [$id, $sig] = explode(':', $decoded, 2);
        $expected = hash_hmac('sha256', $id, (string) config('app.key'));

        return hash_equals($expected, $sig) && is_numeric($id) ? (int) $id : null;
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        if ($field !== null) {
            return parent::resolveRouteBinding($value, $field);
        }

        if (($det = $this->tryDecryptDeterministic((string) $value)) !== null) {
            return static::find($det);
        }

        try {
            $id = (int) Crypt::decryptString((string) $value);

            return static::find($id);
        } catch (\Throwable) {
            if (is_numeric($value)) {
                return static::find((int) $value);
            }

            return null;
        }
    }

    public function getEncryptedIdAttribute(): string
    {
        return $this->getRouteKey();
    }
}
