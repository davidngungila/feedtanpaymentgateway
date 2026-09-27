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
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('network_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sender', 30);
            $table->longText('message_body');
            $table->timestamp('received_at')->nullable();
            $table->char('sms_hash', 64)->index();
            $table->string('transaction_reference', 60)->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('transaction_type', 25)->nullable();
            $table->string('customer_phone', 30)->nullable();
            $table->string('customer_name', 120)->nullable();
            $table->decimal('balance', 15, 2)->nullable();
            $table->string('processing_status', 20)->default('received')->index();
            $table->string('processing_error', 255)->nullable();
            $table->boolean('is_duplicate')->default(false);
            $table->timestamp('server_received_at');
            $table->timestamps();

            $table->unique(['device_id', 'sms_hash']);
            $table->index(['processing_status', 'server_received_at']);
            $table->index(['agent_id', 'server_received_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_messages');
    }
};
