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
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('daily_opening_id')->nullable()->after('agent_id')->constrained()->nullOnDelete();
            $table->decimal('running_cash_balance', 15, 2)->nullable()->after('commission');
            $table->decimal('running_float_balance', 15, 2)->nullable()->after('running_cash_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['daily_opening_id']);
            $table->dropColumn(['daily_opening_id', 'running_cash_balance', 'running_float_balance']);
        });
    }
};
