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
        Schema::create('daily_unique_codes', function (Blueprint $table) {
            $table->id();
            // Kolom ini akan menampung 001, 002, dst.
            $table->string('code', 3)->unique();
            // Tanggal untuk reset harian
            $table->date('date')->index();
            // Status: 0=Available, 1=Used
            $table->boolean('is_used')->default(0);

            // Kombinasi yang memastikan tidak ada kode unik di hari yang sama
            $table->unique(['code', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_unique_codes');
    }
};
