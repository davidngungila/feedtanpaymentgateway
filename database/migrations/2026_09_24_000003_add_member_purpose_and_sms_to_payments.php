<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('description')->nullable()->after('customer_country')->comment('Member purpose e.g. Akiba, Uwekezaji...');
            $table->string('akiba_type')->nullable()->after('description');
            $table->string('uwekezaji_type')->nullable()->after('akiba_type');
            $table->string('hisa_type')->nullable()->after('uwekezaji_type');
            $table->timestamp('sms_sent_at')->nullable()->after('last_verified_at');
            $table->string('sms_message_id')->nullable()->after('sms_sent_at');
            $table->string('sms_status')->nullable()->after('sms_message_id');
            $table->text('sms_text')->nullable()->after('sms_status');
            $table->string('collected_amount')->nullable()->after('amount');
            $table->string('channel_provider')->nullable()->after('channel');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['description','akiba_type','uwekezaji_type','hisa_type','sms_sent_at','sms_message_id','sms_status','sms_text','collected_amount','channel_provider']);
        });
    }
};
