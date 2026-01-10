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
        Schema::create('attendance_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('app');
            $table->integer('role'); // 2: Murid, 3: Guru
            $table->string('day'); // Senin, Selasa, etc.
            $table->time('clock_in_start');
            $table->time('clock_in_end');
            $table->time('clock_out_start');
            $table->time('clock_out_end');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_configs');
    }
};
