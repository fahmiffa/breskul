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
        Schema::create('ujians', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->json('soal_id')->nullable(); // To store multiple question IDs as requested in field list
            $table->unsignedBigInteger('mapel_id');
            $table->unsignedBigInteger('teach_id');

            $table->foreign('mapel_id')->references('id')->on('mapels');
            $table->foreign('teach_id')->references('id')->on('teaches');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ujians');
    }
};
