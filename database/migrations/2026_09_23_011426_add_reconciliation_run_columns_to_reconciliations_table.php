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
        Schema::table('reconciliations', function (Blueprint $table) {
            $table->decimal('cash_deposits', 15, 2)->default(0)->after('opening_cash');
            $table->decimal('cash_withdrawals', 15, 2)->default(0)->after('cash_deposits');
            $table->decimal('opening_float', 15, 2)->default(0)->after('expected_cash');
            $table->decimal('tie_out', 15, 2)->default(0)->after('float_variance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reconciliations', function (Blueprint $table) {
            $table->dropColumn(['cash_deposits', 'cash_withdrawals', 'opening_float', 'tie_out']);
        });
    }
};
