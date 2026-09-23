<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('halaqahs', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->unsignedBigInteger('teach_id');
            $table->string('hari');
            $table->string('waktu');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('teach_id')->references('id')->on('teaches')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('halaqahs');
    }
};
