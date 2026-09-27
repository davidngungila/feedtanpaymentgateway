<?php

namespace App\Support\Security;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

/**
 * Resolves the effective permission set for a user.
 * DB assignments win when present; otherwise the built-in
 * role matrix (Permission::defaults()) applies.
 */
class Permissions
{
    /**
     * @return string[]
     */
    public static function for(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $role = $user->role ?? 'cashier';

        try {
            $db = Role::where('code', $role)->first();
            if ($db && $db->permissions()->exists()) {
                return $db->permissions()->pluck('permissions.code')->all();
            }
        } catch (\Throwable) {
        }

        return Permission::defaults()[$role] ?? [];
    }

    public static function check(?User $user, string $permission): bool
    {
        return in_array($permission, self::for($user), true);
    }
}
