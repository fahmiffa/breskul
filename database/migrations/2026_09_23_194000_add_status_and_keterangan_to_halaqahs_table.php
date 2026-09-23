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
        Schema::table('halaqahs', function (Blueprint $table) {
            $table->boolean('status')->default(1)->after('waktu'); // 1 = Aktif, 0 = Tidak Aktif
            $table->text('keterangan')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('halaqahs', function (Blueprint $table) {
            $table->dropColumn(['status', 'keterangan']);
        });
    }
};
