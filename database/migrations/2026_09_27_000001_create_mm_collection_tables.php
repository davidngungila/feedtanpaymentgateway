<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('providers')) {
            Schema::create('providers', function (Blueprint $table) {
                $table->id();
                $table->string('code', 30)->unique();
                $table->string('name');
                $table->string('color', 20)->nullable();
                $table->string('driver', 30)->default('clickpesa');
                $table->boolean('is_active')->default(true);
                $table->json('config')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('provider_credentials')) {
            Schema::create('provider_credentials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
                $table->string('environment', 20)->default('live');
                $table->string('label')->nullable();
                $table->string('client_id')->nullable();
                $table->text('client_secret')->nullable();
                $table->text('api_key')->nullable();
                $table->text('webhook_secret')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
                $table->unique(['provider_id', 'environment']);
            });
        }

        if (! Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('phone_encrypted');
                $table->string('phone_hash', 64)->unique();
                $table->text('email_encrypted')->nullable();
                $table->string('email_hash', 64)->nullable()->index();
                $table->text('national_id_encrypted')->nullable();
                $table->string('national_id_hash', 64)->nullable()->index();
                $table->text('address_encrypted')->nullable();
                $table->string('status', 20)->default('active')->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('payment_requests')) {
            Schema::create('payment_requests', function (Blueprint $table) {
                $table->id();
                $table->string('reference', 30)->unique();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->string('provider', 30)->index();
                $table->decimal('amount', 15, 2);
                $table->string('currency', 10)->default('TZS');
                $table->string('description')->nullable();
                $table->string('status', 20)->default('pending')->index();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('payment_links')) {
            Schema::create('payment_links', function (Blueprint $table) {
                $table->id();
                $table->string('token', 64)->unique();
                $table->foreignId('payment_request_id')->constrained('payment_requests')->cascadeOnDelete();
                $table->string('status', 20)->default('active')->index();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('redeemed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->string('number', 30)->unique();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('provider', 30)->nullable()->index();
                $table->decimal('amount', 15, 2);
                $table->string('currency', 10)->default('TZS');
                $table->timestamp('due_at')->nullable();
                $table->string('status', 20)->default('issued')->index();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('recurring_collections')) {
            Schema::create('recurring_collections', function (Blueprint $table) {
                $table->id();
                $table->string('reference', 30)->unique();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->string('provider', 30)->index();
                $table->decimal('amount', 15, 2);
                $table->string('currency', 10)->default('TZS');
                $table->string('frequency', 20)->default('monthly');
                $table->timestamp('next_run_at')->nullable();
                $table->timestamp('last_run_at')->nullable();
                $table->string('status', 20)->default('active')->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('provider_transactions')) {
            Schema::create('provider_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('txn_id', 40)->unique();
                $table->string('provider', 30)->index();
                $table->text('provider_transaction_id_encrypted')->nullable();
                $table->string('provider_transaction_id_hash', 64)->nullable();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
                $table->string('phone_hash', 64)->nullable()->index();
                $table->decimal('amount', 15, 2);
                $table->string('currency', 10)->default('TZS');
                $table->string('reference', 40)->nullable()->index();
                $table->decimal('provider_fee', 15, 2)->default(0);
                $table->decimal('net_amount', 15, 2)->default(0);
                $table->string('status', 20)->default('PENDING')->index();
                $table->timestamp('initiated_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->string('webhook_status', 20)->default('pending');
                $table->string('settlement_status', 20)->default('pending')->index();
                $table->string('payload_hash', 64)->nullable();
                $table->timestamps();
                $table->unique(['provider', 'provider_transaction_id_hash'], 'ptx_provider_hash_unique');
            });
        }

        if (! Schema::hasTable('webhook_events')) {
            Schema::create('webhook_events', function (Blueprint $table) {
                $table->id();
                $table->string('provider', 30)->index();
                $table->string('event_id')->nullable();
                $table->string('event_id_hash', 64)->nullable();
                $table->string('signature_status', 20)->default('unverified');
                $table->json('payload_encrypted');
                $table->string('payload_hash', 64);
                $table->timestamp('received_at')->useCurrent();
                $table->timestamp('processed_at')->nullable();
                $table->string('processing_status', 20)->default('received')->index();
                $table->text('error')->nullable();
                $table->timestamps();
                $table->unique(['provider', 'event_id_hash'], 'wh_provider_event_unique');
            });
        }

        if (! Schema::hasTable('refunds')) {
            Schema::create('refunds', function (Blueprint $table) {
                $table->id();
                $table->string('reference', 30)->unique();
                $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
                $table->string('provider', 30)->index();
                $table->decimal('amount', 15, 2);
                $table->text('reason')->nullable();
                $table->string('status', 20)->default('requested')->index();
                $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('provider_reference_encrypted')->nullable();
                $table->string('provider_reference_hash', 64)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('reversals')) {
            Schema::create('reversals', function (Blueprint $table) {
                $table->id();
                $table->string('reference', 30)->unique();
                $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
                $table->string('provider', 30)->index();
                $table->decimal('amount', 15, 2);
                $table->text('reason')->nullable();
                $table->string('status', 20)->default('requested')->index();
                $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('provider_reference_encrypted')->nullable();
                $table->string('provider_reference_hash', 64)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('settlements')) {
            Schema::create('settlements', function (Blueprint $table) {
                $table->id();
                $table->string('reference', 30)->unique();
                $table->string('provider', 30)->nullable()->index();
                $table->timestamp('period_start')->nullable();
                $table->timestamp('period_end')->nullable();
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->decimal('total_fee', 15, 2)->default(0);
                $table->decimal('net_amount', 15, 2)->default(0);
                $table->string('status', 20)->default('draft')->index();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('settlement_items')) {
            Schema::create('settlement_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('settlement_id')->constrained('settlements')->cascadeOnDelete();
                $table->foreignId('provider_transaction_id')->constrained('provider_transactions')->cascadeOnDelete();
                $table->decimal('amount', 15, 2);
                $table->decimal('fee', 15, 2)->default(0);
                $table->decimal('net_amount', 15, 2)->default(0);
                $table->string('status', 20)->default('pending');
                $table->timestamps();
                $table->unique(['settlement_id', 'provider_transaction_id'], 'stl_item_unique');
            });
        }

        if (! Schema::hasTable('api_keys')) {
            Schema::create('api_keys', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('prefix', 20)->index();
                $table->string('key_hash', 64)->unique();
                $table->json('scopes')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('api_request_logs')) {
            Schema::create('api_request_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('api_key_id')->nullable()->constrained('api_keys')->nullOnDelete();
                $table->string('method', 10);
                $table->string('path');
                $table->integer('status')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('security_events')) {
            Schema::create('security_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action', 80)->index();
                $table->string('entity_type', 60)->nullable();
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->json('details')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('result', 20)->default('SUCCESS');
                $table->string('severity', 20)->default('info');
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('code', 30)->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('code', 60)->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('role_permissions')) {
            Schema::create('role_permissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
                $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['role_id', 'permission_id']);
            });
        }

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'provider')) {
                $table->string('provider', 30)->nullable()->index()->after('gateway');
            }
            if (! Schema::hasColumn('payments', 'provider_transaction_id_encrypted')) {
                $table->text('provider_transaction_id_encrypted')->nullable()->after('provider_reference');
            }
            if (! Schema::hasColumn('payments', 'provider_transaction_id_hash')) {
                $table->string('provider_transaction_id_hash', 64)->nullable()->index()->after('provider_transaction_id_encrypted');
            }
            if (! Schema::hasColumn('payments', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('customer_country')->constrained('customers')->nullOnDelete();
            }
            if (! Schema::hasColumn('payments', 'net_amount')) {
                $table->decimal('net_amount', 15, 2)->default(0)->after('fee');
            }
            if (! Schema::hasColumn('payments', 'webhook_status')) {
                $table->string('webhook_status', 20)->default('pending')->after('settlement_status');
            }
            if (! Schema::hasColumn('payments', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('last_verified_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            foreach (['provider', 'provider_transaction_id_encrypted', 'provider_transaction_id_hash', 'customer_id', 'net_amount', 'webhook_status', 'completed_at'] as $col) {
                if (Schema::hasColumn('payments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        foreach (['role_permissions', 'permissions', 'roles', 'security_events', 'api_request_logs', 'api_keys', 'settlement_items', 'settlements', 'reversals', 'refunds', 'webhook_events', 'provider_transactions', 'recurring_collections', 'invoices', 'payment_links', 'payment_requests', 'customers', 'provider_credentials', 'providers'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
