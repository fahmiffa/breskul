<?php

namespace App\Services;

use App\Models\Topup;
use App\Models\Saldo;
use App\Models\LogSaldo;
use App\Jobs\ProcessFcm;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentWebhookService
{
    /**
     * Process the payment webhook data.
     * Verifikasi berdasarkan kode_unik dari Model Topup.
     * Jika cocok dan sukses: update status topup, tambah saldo (kredit), catat LogSaldo, kirim FCM.
     *
     * Format notifikasi yang didukung:
     *  - SMS BSI : "Kredit IDR20,329.00 #Rek XXXXXX4615 ..."
     *  - Format lama: "Rp200.740"
     *
     * @param array $data
     * @return bool
     */
    public function handle(array $data): bool
    {
        Log::channel('payment')->info(json_encode($data));

        $title = $data['title'] ?? '';
        $text = $data['text'] ?? '';
        $content = trim($title . ' ' . $text);

        // 1. Hanya terima uang MASUK. Abaikan notifikasi debit seperti
        //    "You spent IDR 20,329.00 at Account Transfer" dari m-BCA.
        if (!preg_match('/\bKredit\b/i', $content)) {
            return false;
        }

        // 2. Ambil nominal: "IDR20,329.00", "IDR 20.329,00", "Rp200.740"
        if (!preg_match('/(?:IDR|Rp)\s?([\d.,]+)/i', $content, $matches)) {
            return false;
        }

        $totalAmount = $this->parseAmount($matches[1]); // contoh: 20329
        if ($totalAmount <= 0) {
            return false;
        }
        $uniqueCode = $totalAmount % 1000; // contoh: 329

        // 3. Cegah pemrosesan ganda untuk notifikasi yang dikirim ulang
        //    (contoh: SMS yang sama muncul lagi jam 14:28 dengan id & postTime sama).
        $dedupKey = 'payment:notif:' . ($data['id'] ?? 'x') . ':' . ($data['postTime'] ?? md5($content));
        if (!Cache::add($dedupKey, 1, now()->addDay())) {
            Log::channel('payment')->info("PaymentWebhook: Notifikasi duplikat diabaikan ({$dedupKey})");
            return false;
        }

        DB::beginTransaction();
        try {
            // 4. Cari topup pending yang cocok, kunci barisnya agar request
            //    paralel tidak bisa mengkredit dua kali.
            $topup = Topup::where('kode_unik', $uniqueCode)
                ->where('total_nominal', $totalAmount)
                ->where('status', 'pending')
                ->where('expired_at', '>', now())
                ->lockForUpdate()
                ->latest()
                ->first();

            if (!$topup) {
                DB::rollBack();
                // Lepas kunci dedup supaya retry berikutnya tetap bisa diproses
                Cache::forget($dedupKey);
                Log::channel('payment')->warning("PaymentWebhook: Tidak ditemukan topup pending dengan kode unik {$uniqueCode} dan total {$totalAmount}");
                return false;
            }

            $student = $topup->student;
            if (!$student) {
                DB::rollBack();
                Cache::forget($dedupKey);
                Log::channel('payment')->error("PaymentWebhook: Student tidak ditemukan untuk topup ID {$topup->id}");
                return false;
            }

            // 5. Update status topup menjadi success
            $topup->status = 'success';
            $topup->save();

            // 6. Update atau buat saldo siswa (kredit / tambah)
            $saldo = Saldo::firstOrCreate(
                ['students_id' => $student->id],
                [
                    'nominal' => 0,
                    'app_id'  => $student->app ?? null,
                ]
            );

            $nominalTopup = (float) $topup->nominal;
            $saldo->nominal += $nominalTopup;
            $saldo->save();

            // 7. Catat ke LogSaldo sebagai kredit
            LogSaldo::create([
                'saldo_id'    => $saldo->id,
                'students_id' => $student->id,
                'tipe'        => 'kredit',
                'nominal'     => $nominalTopup,
                'keterangan'  => 'Top up saldo via pembayaran (kode: ' . $topup->kode_unik . ')',
            ]);

            DB::commit();

            Log::channel('payment')->info("PaymentWebhook: Topup ID {$topup->id} berhasil. Saldo siswa {$student->name} bertambah Rp " . number_format($nominalTopup, 0, ',', '.') . ". Saldo sekarang: Rp " . number_format($saldo->nominal, 0, ',', '.'));

            // 8. Kirim notifikasi FCM (setelah commit; kegagalan FCM tidak boleh membatalkan topup)
            try {
                $this->sendTopupNotification($topup, $saldo);
            } catch (\Throwable $e) {
                Log::channel('payment')->error("PaymentWebhook: Gagal kirim FCM topup ID {$topup->id}: " . $e->getMessage());
            }

            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            Cache::forget($dedupKey);
            Log::channel('payment')->error("PaymentWebhook: Error saat memproses topup: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ubah string nominal dari berbagai format menjadi integer rupiah.
     *
     * "20,329.00" => 20329
     * "20.329,00" => 20329
     * "200.740"   => 200740
     * "20,329"    => 20329
     */
    private function parseAmount(string $raw): int
    {
        // Buang tanda baca di akhir kalimat (mis. "IDR20,329.")
        $raw = rtrim($raw, '.,');

        // Buang desimal 2 digit di belakang (",00" atau ".00")
        $raw = preg_replace('/[.,]\d{2}$/', '', $raw);

        // Buang pemisah ribuan
        return (int) preg_replace('/[.,]/', '', $raw);
    }

    /**
     * Kirim notifikasi FCM setelah topup saldo berhasil.
     *
     * @param Topup $topup
     * @param Saldo $saldo
     * @return void
     */
    private function sendTopupNotification(Topup $topup, Saldo $saldo): void
    {
        $student = $topup->student;
        if ($student && $student->users && $student->users->fcm) {
            $user = $student->users;

            $topic = 'user_' . $user->id;
            $title = 'Top Up Saldo Berhasil';
            $nominal = number_format($topup->nominal, 0, ',', '.');
            $saldoNow = number_format($saldo->nominal, 0, ',', '.');
            $body = "Top up saldo sebesar Rp {$nominal} berhasil. Saldo Anda sekarang: Rp {$saldoNow}.";

            $message = [
                "topic" => $topic,
                "title" => $title,
                "body"  => $body,
            ];
            ProcessFcm::dispatch($message);
        }
    }
}