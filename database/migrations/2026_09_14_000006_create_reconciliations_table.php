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
        Schema::create('reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained();
            $table->date('reconciliation_date')->index();
            $table->decimal('opening_cash', 15, 2)->default(0);
            $table->decimal('expected_cash', 15, 2)->default(0);
            $table->decimal('counted_cash', 15, 2)->default(0);
            $table->decimal('cash_variance', 15, 2)->default(0);
            $table->decimal('total_float', 15, 2)->default(0);
            $table->decimal('float_variance', 15, 2)->default(0);
            $table->json('network_balances')->nullable();
            $table->string('status', 20)->default('open');
            $table->string('notes', 255)->nullable();
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reconciliations');
    }
};
