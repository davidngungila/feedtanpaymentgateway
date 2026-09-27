<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Idempotent: production previously failed this migration after adding the
     * (empty) column but before the unique index, so the column may already
     * exist with all rows set to ''. Each step is guarded with a schema check.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('devices', 'device_code')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->string('device_code', 8)->after('id');
            });
        }

        $this->backfillDeviceCodes();

        if (! Schema::hasIndex('devices', 'devices_device_code_unique')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->unique('device_code');
            });
        }

        if (Schema::hasColumn('devices', 'api_token_hash')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->renameColumn('api_token_hash', 'authorization_token_hash');
            });
        }
    }

    private function backfillDeviceCodes(): void
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        /** @var Collection<string, bool> $existing */
        $existing = DB::table('devices')
            ->whereNotNull('device_code')
            ->where('device_code', '!=', '')
            ->pluck('device_code')
            ->flip();

        $rows = DB::table('devices')
            ->whereNull('device_code')
            ->orWhere('device_code', '')
            ->orderBy('id')
            ->get();

        foreach ($rows as $row) {
            do {
                $code = '';
                for ($i = 0; $i < 6; $i++) {
                    $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
                }
            } while ($existing->has($code));

            $existing->put($code, true);
            DB::table('devices')->where('id', $row->id)->update(['device_code' => $code]);
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('devices', 'devices_device_code_unique')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->dropUnique('devices_device_code_unique');
            });
        }

        if (Schema::hasColumn('devices', 'device_code')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->dropColumn('device_code');
            });
        }

        if (Schema::hasColumn('devices', 'authorization_token_hash')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->renameColumn('authorization_token_hash', 'api_token_hash');
            });
        }
    }
};
