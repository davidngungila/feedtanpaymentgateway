<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Map the legacy role values to the new tri-role model (cashier / supervisor / admin)
     * and guarantee the three system accounts exist for the live database.
     */
    public function up(): void
    {
        DB::table('users')->where('role', 'manager')->update(['role' => 'supervisor']);
        DB::table('users')->whereIn('role', ['agent', 'viewer'])->update(['role' => 'cashier']);

        $cashPointId = Schema::hasTable('agents')
            ? DB::table('agents')->orderBy('id')->value('id')
            : null;

        $accounts = [
            ['name' => 'Admin System', 'email' => 'admin@moneyagent.local', 'role' => 'admin', 'agent_id' => null],
            ['name' => 'Halima Omary', 'email' => 'supervisor@moneyagent.local', 'role' => 'supervisor', 'agent_id' => null],
            ['name' => 'Baraka Mwenda', 'email' => 'cashier@moneyagent.local', 'role' => 'cashier', 'agent_id' => $cashPointId],
        ];

        foreach ($accounts as $account) {
            $existing = DB::table('users')->where('email', $account['email'])->first();

            if ($existing !== null) {
                DB::table('users')->where('id', $existing->id)->update([
                    'role' => $account['role'],
                    'agent_id' => $account['agent_id'],
                    'is_active' => true,
                ]);
            } else {
                DB::table('users')->insert($account + [
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Single-cash-point system: keep the first agent as the active cash point
        // and retire extras so the UI only ever offers to link the one operator.
        if (Schema::hasTable('agents') && $cashPointId !== null) {
            DB::table('agents')->where('id', '<>', $cashPointId)->update(['status' => 'inactive']);
        }
    }

    /**
     * Best-effort reversal: restore the legacy role names.
     */
    public function down(): void
    {
        DB::table('users')->where('role', 'supervisor')->update(['role' => 'manager']);
        DB::table('users')->where('role', 'cashier')->update(['role' => 'agent']);
    }
};
