<?php

namespace App\Services;

use App\Models\Topup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TopupService
{
    /**
     * Menjalankan auto delete (soft delete) untuk data topup yang sudah mencapai
     * atau melebihi batas waktu expired (expired_at <= now()).
     *
     * @return int Jumlah record topup yang di-delete
     */
    public function deleteExpiredTopups(): int
    {
        $now = Carbon::now();

        $expiredTopups = Topup::where('status', 'pending')
            ->where('expired_at', '<=', $now)
            ->get();

        $count = 0;
        foreach ($expiredTopups as $topup) {
            $topup->status = 'expired';
            $topup->save();
            $topup->delete(); // Soft delete pada model Topup
            $count++;
        }

        if ($count > 0) {
            Log::info("TopupService: {$count} data topup expired berhasil di-soft-delete.");
        }

        return $count;
    }
}
