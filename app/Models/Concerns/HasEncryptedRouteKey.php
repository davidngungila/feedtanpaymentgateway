<?php

namespace App\Models\Concerns;

/**
 * Opaque, tamper-evident route keys. URLs never expose the plain
 * integer ID (no /customers/6) — only an HMAC-signed token that
 * resolves back through decrypt_id().
 */
trait HasEncryptedRouteKey
{
    public function getRouteKey(): string
    {
        return encrypt_id($this->getKey());
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        if ($field !== null) {
            return parent::resolveRouteBinding($value, $field);
        }

        $id = decrypt_id((string) $value);
        if ($id !== null) {
            return static::find($id);
        }

        if (is_numeric($value)) {
            return static::find((int) $value);
        }

        return null;
    }
}
