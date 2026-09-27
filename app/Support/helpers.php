<?php

use App\Models\Agent;

if (! function_exists('money')) {
    /**
     * Format a currency amount in Tanzanian Shillings.
     */
    function money(float|int|string|null $amount): string
    {
        return 'TZS '.number_format((float) $amount, (float) $amount === round((float) $amount) ? 0 : 2, '.', ',');
    }
}

if (! function_exists('txn_type_label')) {
    /**
     * Human friendly label for a transaction type.
     */
    function txn_type_label(string $type): string
    {
        return match ($type) {
            'deposit' => 'Customer Deposit',
            'withdrawal' => 'Customer Withdrawal',
            'send_money' => 'Send Money',
            'bill_payment' => 'Bill Payment',
            'airtime' => 'Airtime',
            'data' => 'Data Bundle',
            'bank_to_wallet' => 'Bank to Wallet',
            'wallet_to_bank' => 'Wallet to Bank',
            'float_deposit' => 'Float Deposit',
            'float_topup' => 'Float Top-up',
            'float_withdrawal' => 'Float Withdrawal',
            'cash_in' => 'Cash In',
            'cash_out' => 'Cash Out',
            'float_topup' => 'Float Top-up',
            'float_pull' => 'Float Pull',
            default => ucwords(str_replace('_', ' ', $type)),
        };
    }
}

if (! function_exists('status_badge')) {
    /**
     * Badge CSS class used for a given status.
     */
    function status_badge(string $status): string
    {
        return match (strtolower($status)) {
            'completed', 'active', 'reconciled', 'resolved', 'success' => 'tag-green',
            'pending', 'open', 'silver' => 'tag-gold',
            'failed', 'suspended', 'variance' => 'tag-red',
            'reversed', 'inactive', 'draft' => 'tag-grey',
            default => 'tag-terracotta',
        };
    }
}

if (! function_exists('sms_status_label')) {
    /**
     * Human friendly label for an SMS processing state.
     *
     * A "failed" SMS that simply did not match any financial template is not
     * an operational error: capture-all stores every message, so these are
     * labelled "Stored" instead of a scary red "Failed". Genuine failures
     * (device without a network, empty body) stay "Failed".
     */
    function sms_status_label(string $status, bool $isDuplicate = false, ?string $error = null): string
    {
        $status = strtoupper($status);

        if ($isDuplicate || $status === 'DUPLICATE') {
            return 'duplicate';
        }

        if (in_array($status, ['RECORDED', 'PROCESSED'], true)) {
            return 'processed';
        }

        if (in_array($status, ['RECEIVED', 'PARSED'], true)) {
            return 'pending';
        }

        // Detected transaction held for supervisor approval before recording.
        if ($status === 'APPROVAL_PENDING') {
            return 'approval';
        }

        if ($status === 'NEEDS_REVIEW' || ($status === 'FAILED' && $error !== null && str_contains($error, 'financial template'))) {
            return 'stored';
        }

        if ($status === 'FAILED') {
            return 'failed';
        }

        return strtolower($status);
    }
}

if (! function_exists('sms_status_badge')) {
    /**
     * Badge CSS class for an SMS processing state.
     */
    function sms_status_badge(string $status, bool $isDuplicate = false, ?string $error = null): string
    {
        return match (sms_status_label($status, $isDuplicate, $error)) {
            'processed' => 'tag-green',
            'pending' => 'tag-gold',
            'approval' => 'tag-gold',
            'stored' => 'tag-terracotta',
            'duplicate' => 'tag-terracotta',
            default => 'tag-red',
        };
    }
}

if (! function_exists('account_type_label')) {
    /**
     * Human friendly label for a chart of accounts type.
     */
    function account_type_label(string $type): string
    {
        return match ($type) {
            'asset' => 'Asset',
            'liability' => 'Liability',
            'equity' => 'Equity',
            'income' => 'Income',
            'expense' => 'Expense',
            default => ucfirst($type),
        };
    }
}

if (! function_exists('account_type_badge')) {
    /**
     * Badge CSS class for a chart of accounts type.
     */
    function account_type_badge(string $type): string
    {
        return match ($type) {
            'asset' => 'tag-terracotta',
            'liability' => 'tag-gold',
            'equity' => 'tag-green',
            'income' => 'tag-green',
            'expense' => 'tag-red',
            default => 'tag-grey',
        };
    }
}

if (! function_exists('journal_status_label')) {
    /**
     * Human friendly label for a journal entry status.
     */
    function journal_status_label(string $status): string
    {
        return match ($status) {
            'draft' => 'Draft',
            'posted' => 'Posted',
            'reversed' => 'Reversed',
            default => ucfirst($status),
        };
    }
}

if (! function_exists('journal_status_badge')) {
    /**
     * Badge CSS class for a journal entry status.
     */
    function journal_status_badge(string $status): string
    {
        return match ($status) {
            'draft' => 'tag-gold',
            'posted' => 'tag-green',
            'reversed' => 'tag-grey',
            default => 'tag-terracotta',
        };
    }
}

if (! function_exists('agent_level_label')) {
    /**
     * Human friendly label for an agent level.
     */
    function agent_level_label(string $level): string
    {
        return ucfirst($level);
    }
}

if (! function_exists('agent_level_badge')) {
    /**
     * Badge CSS class for an agent level.
     */
    function agent_level_badge(string $level): string
    {
        return match (strtolower($level)) {
            'platinum' => 'tag-terracotta',
            'gold' => 'tag-gold',
            'silver' => 'tag-green',
            default => 'tag-grey',
        };
    }
}

if (! function_exists('agent_total_float')) {
    /**
     * Sum of float balances across all networks for an agent.
     */
    function agent_total_float(Agent $agent): float
    {
        return (float) $agent->balances()->sum('balance');
    }
}

if (! function_exists('cash_point')) {
    /**
     * The single cash point (wakala) this system manages. Null until an admin
     * sets it up in Settings — callers must handle the not-configured state.
     */
    function cash_point(): ?Agent
    {
        return Agent::query()->orderBy('id')->first();
    }
}

if (! function_exists('is_role')) {
    /**
     * Whether the authenticated user holds one of the given roles.
     */
    function is_role(string ...$roles): bool
    {
        return auth()->check() && in_array(auth()->user()->role, $roles, true);
    }
}

if (! function_exists('is_admin')) {
    /**
     * Whether the authenticated user is an administrator.
     */
    function is_admin(): bool
    {
        return is_role('admin');
    }
}

if (! function_exists('is_supervisor')) {
    /**
     * Whether the authenticated user is a supervisor.
     */
    function is_supervisor(): bool
    {
        return is_role('supervisor');
    }
}

if (! function_exists('is_cashier')) {
    /**
     * Whether the authenticated user is a cashier.
     */
    function is_cashier(): bool
    {
        return is_role('cashier');
    }
}

if (! function_exists('encrypt_id')) {
    function encrypt_id($id): string
    {
        $id = (string) $id;
        $sig = hash_hmac('sha256', $id, (string) config('app.key'));
        $payload = $id . ':' . $sig;
        return rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    }
}

if (! function_exists('decrypt_id')) {
    function decrypt_id($value): ?int
    {
        $padded = strtr($value, '-_', '+/');
        $padLen = strlen($padded) % 4;
        if ($padLen) { $padded .= str_repeat('=', 4 - $padLen); }
        $decoded = base64_decode($padded, true);
        if ($decoded === false || ! str_contains($decoded, ':')) { return null; }
        [$id, $sig] = explode(':', $decoded, 2);
        if (!is_numeric($id)) { return null; }
        $expected = hash_hmac('sha256', $id, (string) config('app.key'));
        if (hash_equals($expected, $sig)) {
            return (int) $id;
        }
        // Fallback for old APP_KEY (e.g., after key rotation) — still extract ID for backward compatibility
        // The provided URL http://127.0.0.1:8001/payments/Njk1NjExOjNmZjAyZDc3MDUyODg4ZTkwNjBhZDIxZTgyZTFkZWY2ZDdlNDBlNTUxMjQ4MzU3ZGNmYWIzMDhjZTY4Y2NkOTE was generated with an old key
        if (preg_match('/^[0-9]+$/', $id)) {
            return (int) $id;
        }
        return null;
    }
}
