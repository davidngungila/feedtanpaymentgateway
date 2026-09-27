<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('devices', 'encrypted_token')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->dropColumn('encrypted_token');
            });
        }
    }

    public function down(): void
    {
        // Intentionally left empty - dropping the column is not easily reversible
    }
};
