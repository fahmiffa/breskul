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
        Schema::table('attendance_configs', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_configs', 'lat')) {
                $table->string('lat')->nullable();
            }
            if (!Schema::hasColumn('attendance_configs', 'lng')) {
                $table->string('lng')->nullable();
            }
            if (!Schema::hasColumn('attendance_configs', 'radius')) {
                $table->integer('radius')->default(100);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_configs', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng', 'radius']);
        });
    }
};
