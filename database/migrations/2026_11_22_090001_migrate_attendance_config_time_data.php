<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Migrasi data: pindahkan waktu & koordinat dari attendance_configs ke attendance_config_times
        // Default semua hari (Senin - Minggu) agar data existing tetap bisa absen setiap hari
        $configs = DB::table('attendance_configs')->get();

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        foreach ($configs as $config) {
            foreach ($days as $day) {
                DB::table('attendance_config_times')->insert([
                    'attendance_config_id' => $config->id,
                    'day' => $day,
                    'clock_in_start' => $config->clock_in_start ?? '07:00',
                    'clock_in_end' => $config->clock_in_end ?? '08:00',
                    'clock_out_start' => $config->clock_out_start ?? '15:00',
                    'clock_out_end' => $config->clock_out_end ?? '16:00',
                    'lat' => $config->lat,
                    'lng' => $config->lng,
                    'radius' => $config->radius ?? 100,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('attendance_config_times')->truncate();
    }
};
