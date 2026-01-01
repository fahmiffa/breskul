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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user')->nullable();
            $table->bigInteger('app')->nullable();
            $table->string('name');
            $table->string('img')->nullable();
            $table->string('place')->nullable();
            $table->date('birth')->nullable();
            $table->integer('gender')->nullable();
            $table->string('alamat')->nullable();
            $table->string('hp_siswa')->nullable();
            $table->string('agama')->nullable();
            $table->string('dad')->nullable();
            $table->string('dadJob')->nullable();
            $table->string('mom')->nullable();
            $table->string('momJOb')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
