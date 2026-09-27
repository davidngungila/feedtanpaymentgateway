<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_requests', 'purpose')) {
                $table->string('purpose')->nullable()->after('description');
            }
        });

        Schema::table('payment_links', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_links', 'code')) {
                $table->string('code', 10)->nullable()->unique()->after('token');
            }
        });

        // Backfill short opaque codes (e.g. 7K2M9P) for existing links.
        foreach (\App\Models\PaymentLink::whereNull('code')->get() as $link) {
            do {
                $code = strtoupper(Str::random(6));
            } while (\App\Models\PaymentLink::where('code', $code)->exists());
            $link->update(['code' => $code]);
        }

        Schema::table('webhook_events', function (Blueprint $table) {
            if (! Schema::hasColumn('webhook_events', 'exception_type')) {
                $table->string('exception_type', 40)->nullable()->index()->after('processing_status');
            }
        });

        Schema::table('provider_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('provider_transactions', 'exception_type')) {
                $table->string('exception_type', 40)->nullable()->index()->after('settlement_status');
            }
            if (! Schema::hasColumn('provider_transactions', 'expected_amount')) {
                $table->decimal('expected_amount', 15, 2)->nullable()->after('amount');
            }
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_logs', 'old_values')) {
                $table->json('old_values')->nullable()->after('details');
            }
            if (! Schema::hasColumn('audit_logs', 'new_values')) {
                $table->json('new_values')->nullable()->after('old_values');
            }
            if (! Schema::hasColumn('audit_logs', 'user_agent')) {
                $table->string('user_agent', 500)->nullable()->after('ip_address');
            }
            if (! Schema::hasColumn('audit_logs', 'result')) {
                $table->string('result', 20)->default('SUCCESS')->after('user_agent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            foreach (['old_values', 'new_values', 'user_agent', 'result'] as $col) {
                if (Schema::hasColumn('audit_logs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::table('provider_transactions', function (Blueprint $table) {
            foreach (['exception_type', 'expected_amount'] as $col) {
                if (Schema::hasColumn('provider_transactions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::table('webhook_events', function (Blueprint $table) {
            if (Schema::hasColumn('webhook_events', 'exception_type')) {
                $table->dropColumn('exception_type');
            }
        });
        Schema::table('payment_links', function (Blueprint $table) {
            if (Schema::hasColumn('payment_links', 'code')) {
                $table->dropColumn('code');
            }
        });
        Schema::table('payment_requests', function (Blueprint $table) {
            if (Schema::hasColumn('payment_requests', 'purpose')) {
                $table->dropColumn('purpose');
            }
        });
    }
};
