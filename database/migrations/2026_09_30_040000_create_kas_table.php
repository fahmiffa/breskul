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
        Schema::create('kas', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe', ['pemasukan', 'pengeluaran']);
            $table->decimal('nominal', 15, 2);
            $table->string('keterangan');
            $table->string('kategori')->nullable();
            $table->date('tanggal');
            $table->unsignedBigInteger('app')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('referensi')->nullable()->comment('Ref otomatis, misal: bill_id, topup_id');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tipe', 'tanggal']);
            $table->index('app');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas');
    }
};
