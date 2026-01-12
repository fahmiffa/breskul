<?php

namespace App\Services;

use App\Models\DailyUniqueCode;
use App\Models\Bill;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class BillingCodeService
{
    /**
     * Determine the billing date.
     * With 00:00 reset, the billing date is simply Today.
     */
    public function getBillingDate()
    {
        return now()->toDateString();
    }

    /**
     * Menghasilkan atau mereset pool harian jika diperlukan.
     */
    private function ensurePoolIsReady(): void
    {
        $today = $this->getBillingDate();

        // Cek apakah sudah ada kode untuk hari ini
        // Menggunakan lockForUpdate untuk mencegah race condition saat pembuatan pool
        if (DailyUniqueCode::where('date', $today)->count() === 0) {
             DB::transaction(function () use ($today) {
                // Double check inside transaction
                if (DailyUniqueCode::where('date', $today)->count() === 0) {
                    $codes = [];
                    // Generate codes 001 to 999 (avoid 000 if preferred, or include it)
                    // Request asked for 3 digits, usually 1-999 or 0-999.
                    for ($i = 1; $i < 1000; $i++) {
                        $codes[] = [
                            'code' => str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                            'date' => $today,
                            'is_used' => false,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    DailyUniqueCode::insert($codes);
                }
             });
        }
    }

    /**
     * Menghasilkan satu kode unik 3 digit dengan jaminan keunikan.
     * @throws \Exception Jika pool kode harian habis.
     */
    public function generateUniqueCode($billId): string
    {
        // $this->ensurePoolIsReady();
        $today = now()->toDateString();
        $code = null;

        // Mulai Transaksi Database (Kritis untuk Syarat 3: Tidak Duplikasi)
        DB::transaction(function () use ($today, &$code, $billId) {
            
            // 1. Cari satu kode yang BELUM DIGUNAKAN secara acak.
            //    FOR UPDATE: Mengunci baris ini agar Request lain tidak bisa membacanya 
            //    sebelum transaksi ini selesai.
            $record = DailyUniqueCode::where('date', $today)
                                     ->where('is_used', false)
                                     ->inRandomOrder()
                                     ->lockForUpdate()
                                     ->first();

            if ($record) {
                // 2. Tandai sebagai sudah digunakan.
                $record->is_used = true;
                $record->bill_id = $billId;
                $record->save();
                
                $code = $record->code;

                // 3. Update Bill dengan kode unik dan waktu expiry
                // Expired jam 23:00 hari ini
                $expiredAt = Carbon::now()->setTime(23, 0, 0);
                
                Bill::where('id', $billId)->update([
                    'unique_code' => $record->code,
                    'qris_expired_at' => $expiredAt,
                    // qris_data updated outside this or passed in?
                    // Usually controller updates qris_data after getting code. 
                ]);
            }
        });

        if (is_null($code)) {
            Log::critical('Pool kode unik database habis!', ['date' => $today]);
            throw new Exception('Quota kode pembayaran sudah habis');
        }

        return $code;
    }
}