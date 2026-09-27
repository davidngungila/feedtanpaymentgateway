<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('device_network', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('network_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['device_id', 'network_id']);
        });

        DB::table('devices')
            ->select('id', 'network_id')
            ->whereNotNull('network_id')
            ->orderBy('id')
            ->chunkById(500, function ($devices) {
                foreach ($devices as $device) {
                    DB::table('device_network')->insert([
                        'device_id' => $device->id,
                        'network_id' => $device->network_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_network');
    }
};
