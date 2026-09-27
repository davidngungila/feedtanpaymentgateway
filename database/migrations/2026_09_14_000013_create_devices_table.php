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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_uid', 80)->unique()->nullable();
            $table->string('name', 120);
            $table->string('model', 120)->nullable();
            $table->foreignId('agent_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('network_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone_number', 30)->nullable();
            $table->string('sim_number', 60)->nullable();
            $table->string('android_version', 30)->nullable();
            $table->string('app_version', 30)->nullable();
            $table->string('api_token_hash', 64)->unique()->nullable();
            $table->string('branch', 120)->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->string('last_ip', 45)->nullable();
            $table->timestamp('last_sms_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'updated_at']);
            $table->index(['agent_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
