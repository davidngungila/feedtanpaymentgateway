<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->foreignId('device_line_id')->nullable()->after('device_id')->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('sim_slot')->nullable()->after('device_line_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('device_line_id');
            $table->dropColumn('sim_slot');
        });
    }
};
