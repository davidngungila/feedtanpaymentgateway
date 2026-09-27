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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('owner_name')->nullable();
            $table->string('phone', 30);
            $table->string('national_id', 40)->nullable();
            $table->string('region', 80)->nullable();
            $table->string('district', 80)->nullable();
            $table->string('ward', 80)->nullable();
            $table->string('street', 120)->nullable();
            $table->string('agent_level', 20)->default('bronze');
            $table->string('status', 20)->default('active');
            $table->decimal('cash_balance', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
