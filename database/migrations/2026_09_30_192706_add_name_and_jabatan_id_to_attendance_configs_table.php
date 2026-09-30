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
            $table->string('name')->nullable()->after('app');
            $table->foreignId('jabatan_id')->nullable()->after('name')->constrained('jabatans')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_configs', function (Blueprint $table) {
            $table->dropForeign(['jabatan_id']);
            $table->dropColumn(['name', 'jabatan_id']);
        });
    }
};
