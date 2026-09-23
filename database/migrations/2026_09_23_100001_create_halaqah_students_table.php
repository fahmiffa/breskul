<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('halaqah_students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('halaqah_id');
            $table->unsignedBigInteger('students_id');
            $table->timestamp('present_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('halaqah_id')->references('id')->on('halaqahs')->onDelete('cascade');
            $table->foreign('students_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('halaqah_students');
    }
};
