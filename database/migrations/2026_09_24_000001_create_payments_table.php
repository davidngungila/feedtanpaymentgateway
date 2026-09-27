<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('gateway')->default('clickpesa')->index();
            $table->string('method')->default('mobile_money');
            $table->string('channel')->default('API');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('customer_country')->default('TZ');
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2)->default(0);
            $table->string('currency')->default('TZS');
            $table->string('status')->default('pending')->index();
            $table->string('gateway_reference')->nullable()->index();
            $table->string('provider_reference')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('notes')->nullable();
            $table->string('settlement_status')->default('pending');
            $table->timestamp('settlement_date')->nullable();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('raw_payload')->nullable();
            $table->json('webhooks')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
