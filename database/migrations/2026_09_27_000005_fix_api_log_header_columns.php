<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provider_api_logs', function (Blueprint $table) {
            $table->text('request_headers_encrypted')->nullable()->change();
            $table->text('response_headers_encrypted')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('provider_api_logs', function (Blueprint $table) {
            $table->json('request_headers_encrypted')->nullable()->change();
            $table->json('response_headers_encrypted')->nullable()->change();
        });
    }
};
