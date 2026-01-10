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
        Schema::table('bills', function (Blueprint $table) {
            $table->string('unique_code')->nullable()->after('status');
            $table->text('qris_data')->nullable()->after('unique_code');
            $table->timestamp('qris_expired_at')->nullable()->after('qris_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn(['unique_code', 'qris_data', 'qris_expired_at']);
        });
    }
};
