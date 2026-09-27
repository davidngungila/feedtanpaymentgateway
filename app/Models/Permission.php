<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['code', 'name', 'description'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    /**
     * Canonical permission catalogue for the collection platform.
     *
     * @return array<string, string> code => description
     */
    public static function catalogue(): array
    {
        return [
            'payments.view' => 'View all transactions',
            'payments.initiate' => 'Initiate a collection (USSD push)',
            'payments.refund.request' => 'Request a refund',
            'payments.refund.approve' => 'Approve a refund (supervisor+)',
            'payments.reverse' => 'Record a reversal (supervisor+)',
            'customers.view' => 'View customers (masked)',
            'customers.reveal' => 'Reveal full customer phone (audited)',
            'providers.view' => 'View provider pages',
            'providers.configure' => 'Change provider configuration (admin)',
            'credentials.view' => 'View credential metadata (admin, audited)',
            'credentials.manage' => 'Create/rotate credentials (admin, audited)',
            'reconciliation.review' => 'Review unmatched transactions',
            'reconciliation.approve' => 'Approve reconciliation (supervisor+)',
            'settlements.manage' => 'Create settlement batches',
            'settlements.approve' => 'Approve settlements (admin)',
            'developers.keys' => 'Manage API keys (admin)',
            'reports.view' => 'View reports',
            'reports.export' => 'Export reports (CSV)',
            'users.manage' => 'Manage users (admin)',
            'roles.manage' => 'Manage roles & permissions (admin)',
            'audit.view' => 'View audit + security events (supervisor+)',
            'settings.manage' => 'Manage system settings (admin)',
        ];
    }

    /**
     * Default grants per system role.
     *
     * @return array<string, string[]>
     */
    public static function defaults(): array
    {
        $all = array_keys(self::catalogue());
        $supervisor = array_filter($all, fn ($c) => ! in_array($c, [
            'providers.configure', 'credentials.view', 'credentials.manage',
            'settlements.approve', 'developers.keys', 'users.manage',
            'roles.manage', 'settings.manage',
        ], true));
        $cashier = [
            'payments.view', 'payments.initiate', 'payments.refund.request',
            'customers.view', 'providers.view', 'reconciliation.review', 'reports.view',
        ];

        return ['admin' => $all, 'supervisor' => array_values($supervisor), 'cashier' => $cashier];
    }
}
