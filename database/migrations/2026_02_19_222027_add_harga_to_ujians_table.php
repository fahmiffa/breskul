<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->boolean('is_paid')->default(false)->after('teach_id')->comment('0=gratis, 1=berbayar');
            $table->bigInteger('harga')->default(0)->after('is_paid')->comment('Harga ujian dalam rupiah');
        });
    }

    public function down(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->dropColumn(['is_paid', 'harga']);
        });
    }
};
