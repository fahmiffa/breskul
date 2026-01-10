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
        Schema::table('presents', function (Blueprint $table) {
            $table->unsignedBigInteger('teacher_id')->nullable()->after('student_id');
            $table->string('status')->nullable()->after('waktu'); // masuk, pulang
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presents', function (Blueprint $table) {
            $table->dropColumn(['teacher_id', 'status']);
        });
    }
};
