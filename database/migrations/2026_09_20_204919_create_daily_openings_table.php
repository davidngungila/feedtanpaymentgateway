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
        Schema::create('daily_openings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // cashier who entered
            $table->date('opening_date');
            $table->decimal('cash_opening', 15, 2)->default(0);
            $table->json('float_openings'); // network_id => amount
            $table->text('notes')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->decimal('cash_closing', 15, 2)->nullable();
            $table->json('float_closings')->nullable();
            $table->decimal('total_volume', 15, 2)->default(0);
            $table->decimal('total_commission', 15, 2)->default(0);
            $table->integer('total_transactions')->default(0);
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->unique(['agent_id', 'opening_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_openings');
    }
};
