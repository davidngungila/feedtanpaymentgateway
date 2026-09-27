<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('beneficiary_name');
            $table->string('beneficiary_account')->nullable();
            $table->string('beneficiary_phone')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('method')->default('mobile_money')->index();
            $table->string('gateway')->default('clickpesa')->index();
            $table->string('gateway_reference')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2)->default(0);
            $table->string('currency')->default('TZS');
            $table->string('purpose')->default('other');
            $table->text('notes')->nullable();
            $table->string('source_account')->default('settlement');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('failure_reason')->nullable();
            $table->text('approval_notes')->nullable();
            $table->string('gateway_status')->nullable();
            $table->string('gateway_latency')->nullable();
            $table->json('gateway_payload')->nullable();
            $table->text('gateway_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
