<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the authorization-token columns: authentication is now device-code only.
     * Idempotent: guards every column in case a variant of the schema is applied,
     * and drops any indexes that reference the removed columns first (SQLite
     * requires this before a column can be dropped).
     */
    public function up(): void
    {
        $columns = ['authorization_token_hash', 'authorization_token_encrypted', 'encrypted_token'];

        $columnsToDrop = array_values(array_filter($columns, fn (string $column): bool => Schema::hasColumn('devices', $column)));

        if ($columnsToDrop === []) {
            return;
        }

        foreach (Schema::getIndexes('devices') as $index) {
            $referencesColumn = array_intersect($index['columns'] ?? [], $columnsToDrop);

            if ($referencesColumn === []) {
                continue;
            }

            Schema::table('devices', function (Blueprint $table) use ($index): void {
                $table->dropIndex($index['name']);
            });
        }

        Schema::table('devices', function (Blueprint $table) use ($columnsToDrop): void {
            $table->dropColumn($columnsToDrop);
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table): void {
            if (! Schema::hasColumn('devices', 'authorization_token_hash')) {
                $table->string('authorization_token_hash', 64)->unique()->nullable()->after('device_code');
            }
            if (! Schema::hasColumn('devices', 'authorization_token_encrypted')) {
                $table->text('authorization_token_encrypted')->nullable()->after('authorization_token_hash');
            }
            if (! Schema::hasColumn('devices', 'encrypted_token')) {
                $table->text('encrypted_token')->nullable()->after('authorization_token_encrypted');
            }
        });
    }
};
