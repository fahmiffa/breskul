<?php

namespace App\Services;

use App\Models\DailyUniqueCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class BillingCodeService
{
    /**
     * Menghasilkan atau mereset pool harian jika diperlukan.
     */
    private function ensurePoolIsReady(): void
    {
        $today = now()->toDateString();

        // Cek apakah sudah ada kode untuk hari ini
        if (DailyUniqueCode::where('date', $today)->count() === 0) {
            // Jika tidak ada, jalankan reset (Syarat 2: Reset Harian)
            $codes = [];
            for ($i = 0; $i < 1000; $i++) {
                $codes[] = [
                    'code' => str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                    'date' => $today,
                    'is_used' => false,
                ];
            }

            // Masukkan 1000 kode baru sekaligus
            DailyUniqueCode::insert($codes);
        }
    }

    /**
     * Menghasilkan satu kode unik 3 digit dengan jaminan keunikan.
     * @throws \Exception Jika pool kode harian habis.
     */
    public function generateUniqueCode(): string
    {
        $this->ensurePoolIsReady();
        $today = now()->toDateString();
        $code = null;

        // Mulai Transaksi Database (Kritis untuk Syarat 3: Tidak Duplikasi)
        DB::transaction(function () use ($today, &$code) {
            
            // 1. Cari satu kode yang BELUM DIGUNAKAN secara acak.
            //    FOR UPDATE: Mengunci baris ini agar Request lain tidak bisa membacanya 
            //    sebelum transaksi ini selesai. (Mencegah Race Condition/Duplikasi)
            $record = DailyUniqueCode::where('date', $today)
                                     ->where('is_used', false)
                                     ->inRandomOrder() // Syarat 1: Kode Unik Acak
                                     ->lockForUpdate()
                                     ->first();

            if ($record) {
                // 2. Tandai sebagai sudah digunakan.
                $record->is_used = true;
                $record->save();
                $code = $record->code;
            } else {
                // Jika tidak ada, pool habis (akan ditangani di luar transaction)
            }
        });

        if (is_null($code)) {
            Log::critical('Pool kode unik database habis!', ['date' => $today]);
            throw new Exception('Batas 1000 kode unik harian telah tercapai (Database).');
        }

        return $code;
    }
}