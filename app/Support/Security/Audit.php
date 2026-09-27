<?php

namespace App\Support\Security;

use App\Models\AuditLog;
use App\Models\SecurityEvent;
use Illuminate\Database\Eloquent\Model;

/**
 * Dual audit trail:
 * - audit_logs: general operational history (existing table).
 * - security_events: sensitive actions, append-only, no edit UI.
 */
class Audit
{
    /**
     * Actions that must ALSO land in security_events.
     */
    public const SENSITIVE = [
        'auth.login.failed',
        'provider.credential.viewed',
        'provider.credential.changed',
        'provider.config.changed',
        'customer.revealed',
        'api_key.created',
        'api_key.revealed',
        'api_key.revoked',
        'webhook.rejected',
        'webhook.verified',
        'payment.refund.requested',
        'payment.refund.approved',
        'payment.reversed',
        'reconciliation.approved',
        'settlement.approved',
        'role.changed',
        'permission.changed',
    ];

    public static function log(string $action, Model|string|null $entity = null, array $details = [], string $result = 'SUCCESS', string $severity = 'info', ?array $old = null, ?array $new = null): void
    {
        $details = Crypto::scrub($details);

        [$type, $id] = self::entityRef($entity);

        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'entity_type' => $type,
                'entity_id' => $id,
                'details' => $details,
                'old_values' => $old ? Crypto::scrub($old) : null,
                'new_values' => $new ? Crypto::scrub($new) : null,
                'ip_address' => request()->ip(),
                'user_agent' => mb_substr((string) request()->userAgent(), 0, 500),
                'result' => $result,
            ]);
        } catch (\Throwable) {
            // Audit must never break the request.
        }

        if (in_array($action, self::SENSITIVE, true)) {
            try {
                SecurityEvent::create([
                    'user_id' => auth()->id(),
                    'action' => $action,
                    'entity_type' => $type,
                    'entity_id' => $id,
                    'details' => $details,
                    'ip_address' => request()->ip(),
                    'result' => $result,
                    'severity' => $severity,
                ]);
            } catch (\Throwable) {
            }
        }
    }

    /**
     * @return array{0: string|null, 1: int|null}
     */
    protected static function entityRef(Model|string|null $entity): array
    {
        if ($entity instanceof Model) {
            return [$entity->getMorphClass(), is_numeric($entity->getKey()) ? (int) $entity->getKey() : null];
        }

        if (is_string($entity)) {
            return [$entity, null];
        }

        return [null, null];
    }
}
