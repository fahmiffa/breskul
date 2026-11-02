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
        Schema::table('devices', function (Blueprint $table) {
            if (!Schema::hasColumn('devices', 'status')) {
                $table->integer('status')->default(0)->after('device_name')->comment('0: startup, 1: stand by');
            }
            if (!Schema::hasColumn('devices', 'ip_address')) {
                $table->string('ip_address')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn(['status', 'ip_address']);
        });
    }
};
