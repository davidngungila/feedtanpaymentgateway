<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('provider_error_codes')) {
            Schema::create('provider_error_codes', function (Blueprint $table) {
                $table->id();
                $table->string('provider', 30)->index();
                $table->string('code', 30);
                $table->string('description');
                $table->string('internal_status', 20)->index();
                $table->boolean('retryable')->default(false);
                $table->boolean('requires_manual_review')->default(false);
                $table->timestamps();
                $table->unique(['provider', 'code']);
            });
        }

        if (! Schema::hasTable('provider_api_logs')) {
            Schema::create('provider_api_logs', function (Blueprint $table) {
                $table->id();
                $table->string('provider', 30)->index();
                $table->string('transaction_id', 60)->nullable()->index();
                $table->string('operation', 40)->index();
                $table->integer('http_status')->nullable();
                $table->integer('duration_ms')->nullable();
                $table->unsignedInteger('attempt')->default(1);
                $table->boolean('success')->default(false);
                $table->json('request_headers_encrypted')->nullable();
                $table->text('request_body_encrypted')->nullable();
                $table->json('response_headers_encrypted')->nullable();
                $table->text('response_body_encrypted')->nullable();
                $table->text('error')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('disbursements')) {
            Schema::create('disbursements', function (Blueprint $table) {
                $table->id();
                $table->string('provider', 30)->index();
                $table->string('reference', 40)->unique();
                $table->string('internal_reference', 40)->unique();
                $table->text('recipient_encrypted');
                $table->string('recipient_hash', 64)->index();
                $table->string('recipient_last4', 4)->nullable();
                $table->decimal('amount', 15, 2);
                $table->string('currency', 10)->default('TZS');
                $table->text('sender_name_encrypted')->nullable();
                $table->string('brand_id')->nullable();
                $table->string('language', 10)->nullable();
                $table->string('status', 20)->default('PENDING')->index();
                $table->text('provider_txnid_encrypted')->nullable();
                $table->string('provider_txnid_hash', 64)->nullable();
                $table->text('provider_refid_encrypted')->nullable();
                $table->string('provider_refid_hash', 64)->nullable();
                $table->text('message')->nullable();
                $table->string('error_code', 30)->nullable()->index();
                $table->string('exception_type', 40)->nullable()->index();
                $table->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->unique(['provider', 'provider_txnid_hash'], 'dis_provider_txn_unique');
            });
        }

        Schema::table('provider_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('provider_transactions', 'internal_reference')) {
                $table->string('internal_reference', 40)->nullable()->unique()->after('txn_id');
            }
            if (! Schema::hasColumn('provider_transactions', 'customer_reference_id')) {
                $table->string('customer_reference_id', 60)->nullable()->index()->after('reference');
            }
            if (! Schema::hasColumn('provider_transactions', 'provider_refid_encrypted')) {
                $table->text('provider_refid_encrypted')->nullable()->after('provider_transaction_id_encrypted');
            }
            if (! Schema::hasColumn('provider_transactions', 'provider_refid_hash')) {
                $table->string('provider_refid_hash', 64)->nullable()->index()->after('provider_transaction_id_hash');
            }
            if (! Schema::hasColumn('provider_transactions', 'msisdn_encrypted')) {
                $table->text('msisdn_encrypted')->nullable()->after('phone_hash');
            }
            if (! Schema::hasColumn('provider_transactions', 'phone_last4')) {
                $table->string('phone_last4', 4)->nullable()->after('msisdn_encrypted');
            }
            if (! Schema::hasColumn('provider_transactions', 'sender_name_encrypted')) {
                $table->text('sender_name_encrypted')->nullable()->after('phone_last4');
            }
            if (! Schema::hasColumn('provider_transactions', 'sender_name_confirmed')) {
                $table->string('sender_name_confirmed')->nullable()->after('sender_name_encrypted');
            }
            if (! Schema::hasColumn('provider_transactions', 'idempotency_key')) {
                $table->string('idempotency_key', 80)->nullable()->unique()->after('payload_hash');
            }
        });

        Schema::table('payment_attempts', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_attempts', 'attempt_number')) {
                $table->unsignedInteger('attempt_number')->default(1)->after('action');
            }
            if (! Schema::hasColumn('payment_attempts', 'request_id')) {
                $table->string('request_id', 80)->nullable()->after('attempt_number');
            }
            if (! Schema::hasColumn('payment_attempts', 'status')) {
                $table->string('status', 20)->nullable()->after('success');
            }
            if (! Schema::hasColumn('payment_attempts', 'error_code')) {
                $table->string('error_code', 30)->nullable()->after('status');
            }
            if (! Schema::hasColumn('payment_attempts', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('error');
            }
            if (! Schema::hasColumn('payment_attempts', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('started_at');
            }
            if (! Schema::hasColumn('payment_attempts', 'duration_ms')) {
                $table->integer('duration_ms')->nullable()->after('completed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_attempts', function (Blueprint $table) {
            foreach (['attempt_number', 'request_id', 'status', 'error_code', 'started_at', 'completed_at', 'duration_ms'] as $col) {
                if (Schema::hasColumn('payment_attempts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::table('provider_transactions', function (Blueprint $table) {
            foreach (['internal_reference', 'customer_reference_id', 'provider_refid_encrypted', 'provider_refid_hash', 'msisdn_encrypted', 'phone_last4', 'sender_name_encrypted', 'sender_name_confirmed', 'idempotency_key'] as $col) {
                if (Schema::hasColumn('provider_transactions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::dropIfExists('disbursements');
        Schema::dropIfExists('provider_api_logs');
        Schema::dropIfExists('provider_error_codes');
    }
};
