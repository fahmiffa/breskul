<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ujian_students', function (Blueprint $table) {
            $table->tinyInteger('payment_status')->default(0)->after('status')->comment('0=belum bayar, 1=sudah bayar');
            $table->string('unique_code')->nullable()->after('payment_status');
            $table->text('qris_data')->nullable()->after('unique_code');
            $table->timestamp('qris_expired_at')->nullable()->after('qris_data');
        });
    }

    public function down(): void
    {
        Schema::table('ujian_students', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'unique_code', 'qris_data', 'qris_expired_at']);
        });
    }
};
