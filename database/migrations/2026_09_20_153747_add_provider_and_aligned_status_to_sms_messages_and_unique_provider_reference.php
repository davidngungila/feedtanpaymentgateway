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
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->string('provider', 30)->nullable()->after('sender')->index();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->unique(['network_id', 'provider_reference'], 'transactions_network_provider_reference_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique('transactions_network_provider_reference_unique');
        });

        Schema::table('sms_messages', function (Blueprint $table) {
            $table->dropIndex('sms_messages_provider_index');
            $table->dropColumn('provider');
        });
    }
};
