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
        Schema::create('reconciliation_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconciliation_id')->constrained()->cascadeOnDelete();
            $table->enum('scope', ['cash', 'float']);
            $table->foreignId('network_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 40);
            $table->string('reference', 120);
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reconciliation_corrections');
    }
};
