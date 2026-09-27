<?php

namespace App\Models;

use App\Models\Concerns\HasEncryptedRouteKey;
use App\Support\Security\Crypto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasEncryptedRouteKey;
    protected $fillable = ['name', 'prefix', 'key_hash', 'scopes', 'last_used_at', 'expires_at', 'is_active', 'created_by'];

    protected function casts(): array
    {
        return ['scopes' => 'array', 'last_used_at' => 'datetime', 'expires_at' => 'datetime', 'is_active' => 'boolean'];
    }

    protected $hidden = ['key_hash'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ApiRequestLog::class);
    }

    /**
     * Mint a key. Only the returned plaintext is ever shown — the DB
     * keeps prefix + HMAC hash, so a leak reveals no active keys.
     *
     * @return array{model:self,plaintext:string}
     */
    public static function mint(string $name, ?User $creator = null, ?array $scopes = null): array
    {
        $prefix = 'pk_live_'.Str::upper(Str::random(4));
        $plaintext = $prefix.'_'.Str::random(32);

        $model = static::create([
            'name' => $name,
            'prefix' => $prefix,
            'key_hash' => Crypto::blindIndex('api', $plaintext),
            'scopes' => $scopes,
            'created_by' => $creator?->id,
        ]);

        return ['model' => $model, 'plaintext' => $plaintext];
    }

    public static function findByKey(string $plaintext): ?self
    {
        return static::where('key_hash', Crypto::blindIndex('api', $plaintext))
            ->where('is_active', true)
            ->first();
    }
}
