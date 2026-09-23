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
        Schema::create('log_saldos', function (Blueprint $table) {
            $table->id();
            $table->text('keterangan')->nullable();
            $table->enum('tipe', ['kredit', 'debit']);
            $table->decimal('nominal', 15, 2);
            $table->unsignedBigInteger('saldo_id');
            $table->unsignedBigInteger('students_id');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('saldo_id')->references('id')->on('saldos')->onDelete('cascade');
            $table->foreign('students_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_saldos');
    }
};
