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
        Schema::table('float_transactions', function (Blueprint $table) {
            $table->foreignId('daily_opening_id')->nullable()->after('agent_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('float_transactions', function (Blueprint $table) {
            $table->dropForeign(['daily_opening_id']);
            $table->dropColumn('daily_opening_id');
        });
    }
};
