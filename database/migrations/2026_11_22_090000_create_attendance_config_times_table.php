<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_config_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_config_id')->constrained('attendance_configs')->cascadeOnDelete();
            $table->string('day'); // Senin, Selasa, Rabu, Kamis, Jumat, Sabtu, Minggu
            $table->time('clock_in_start');
            $table->time('clock_in_end');
            $table->time('clock_out_start');
            $table->time('clock_out_end');
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->integer('radius')->default(100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_config_times');
    }
};
