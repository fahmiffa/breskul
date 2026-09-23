<?php

namespace App\Services;

use App\Models\Topup;
use App\Models\Saldo;
use App\Models\LogSaldo;
use App\Jobs\ProcessFcm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentWebhookService
{
    /**
     * Process the payment webhook data.
     * Verifikasi berdasarkan kode_unik dari Model Topup.
     * Jika cocok dan sukses: update status topup, tambah saldo (kredit), catat LogSaldo, kirim FCM.
     *
     * @param array $data
     * @return bool
     */
    public function handle(array $data): bool
    {
        Log::channel('payment')->info(json_encode($data));

        $title = $data['title'] ?? '';
        $text = $data['text'] ?? '';
        $content = $title . ' ' . $text;

        // Pattern to extract amount like Rp200.740
        if (preg_match('/Rp([\d\.]+)/', $content, $matches)) {
            $amountStr = str_replace('.', '', $matches[1]); // e.g. 200740
            $uniqueCode = (int) substr($amountStr, -3); // e.g. 740
            $totalAmount = (int) $amountStr;

            // Find topup with this kode_unik that is still pending and not expired
            $topup = Topup::where('kode_unik', $uniqueCode)
                ->where('status', 'pending')
                ->where('expired_at', '>', now())
                ->latest()
                ->first();

            if (!$topup) {
                Log::channel('payment')->warning("PaymentWebhook: Tidak ditemukan topup pending dengan kode unik {$uniqueCode}");
                return false;
            }

            // Verifikasi total nominal cocok
            if ((int) $topup->total_nominal !== $totalAmount) {
                Log::channel('payment')->warning("PaymentWebhook: Nominal tidak cocok. Topup expects {$topup->total_nominal}, received {$totalAmount}");
                return false;
            }

            DB::beginTransaction();
            try {
                // 1. Update status topup menjadi success
                $topup->status = 'success';
                $topup->save();

                $student = $topup->student;
                if (!$student) {
                    DB::rollBack();
                    Log::channel('payment')->error("PaymentWebhook: Student tidak ditemukan untuk topup ID {$topup->id}");
                    return false;
                }

                // 2. Update atau buat saldo siswa (kredit / tambah)
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

                // 3. Catat ke LogSaldo sebagai kredit
                LogSaldo::create([
                    'saldo_id'    => $saldo->id,
                    'students_id' => $student->id,
                    'tipe'        => 'kredit',
                    'nominal'     => $nominalTopup,
                    'keterangan'  => 'Top up saldo via pembayaran (kode: ' . $topup->kode_unik . ')',
                ]);

                DB::commit();

                Log::channel('payment')->info("PaymentWebhook: Topup ID {$topup->id} berhasil. Saldo siswa {$student->name} bertambah Rp " . number_format($nominalTopup, 0, ',', '.') . ". Saldo sekarang: Rp " . number_format($saldo->nominal, 0, ',', '.'));

                // 4. Kirim notifikasi FCM
                $this->sendTopupNotification($topup, $saldo);

                return true;
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::channel('payment')->error("PaymentWebhook: Error saat memproses topup ID {$topup->id}: " . $e->getMessage());
                return false;
            }
        }

        return false;
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
