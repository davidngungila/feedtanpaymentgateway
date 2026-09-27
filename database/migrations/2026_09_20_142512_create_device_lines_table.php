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
        Schema::create('device_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('sim_slot')->default(1);
            $table->foreignId('network_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone_number', 30)->nullable();
            $table->string('subscription_id', 30)->nullable();
            $table->timestamps();

            $table->unique(['device_id', 'sim_slot']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_lines');
    }
};
