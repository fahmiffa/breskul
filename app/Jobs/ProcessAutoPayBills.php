<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Bill;
use App\Models\LogSaldo;
use App\Jobs\ProcessFcm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessAutoPayBills implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Ambil semua tagihan yang belum lunas (status = 0)
        // Gunakan chunk untuk memproses data dalam batch
        Bill::with(['payment', 'head.murid.saldo', 'head.murid.users'])
            ->where('status', 0)
            ->chunk(100, function ($bills) {
                foreach ($bills as $bill) {
                    try {
                        DB::beginTransaction();

                        $payment = $bill->payment;
                        $head = $bill->head;

                        // Jika relasi data tidak lengkap, skip
                        if (!$payment || !$head) {
                            DB::rollBack();
                            continue;
                        }

                        $student = $head->murid;
                        if (!$student) {
                            DB::rollBack();
                            continue;
                        }

                        $saldo = $student->saldo;
                        if (!$saldo) {
                            DB::rollBack();
                            continue;
                        }

                        $nominalTagihan = (float) $payment->nominal;
                        
                        // Periksa apakah saldo cukup untuk membayar tagihan
                        if ((float) $saldo->nominal >= $nominalTagihan) {
                            // 1. Potong Saldo
                            $saldo->nominal -= $nominalTagihan;
                            $saldo->save();

                            // 2. Ubah status Bill menjadi lunas (1)
                            $bill->status = 1;
                            $bill->save();

                            // 3. Simpan Riwayat di LogSaldo
                            LogSaldo::create([
                                'keterangan'  => 'Pembayaran otomatis tagihan: ' . ($payment->name ?? 'Tagihan'),
                                'tipe'        => 'debit',
                                'nominal'     => $nominalTagihan,
                                'saldo_id'    => $saldo->id,
                                'students_id' => $student->id,
                            ]);

                            DB::commit();
                            Log::info("AutoPay: Berhasil bayar tagihan ID {$bill->id} untuk siswa {$student->name}");

                            // 4. (Opsional) Kirim Notifikasi FCM ke user siswa
                            if ($student->users) {
                                $message = [
                                    'topic' => 'user_' . $student->users->id,
                                    'title' => 'Pembayaran Otomatis Berhasil',
                                    'body'  => 'Tagihan ' . ($payment->name ?? '') . ' sebesar Rp ' . number_format($nominalTagihan, 0, ',', '.') . ' telah berhasil dipotong dari saldo Anda.',
                                ];
                                ProcessFcm::dispatch($message);
                            }
                        } else {
                            DB::rollBack();
                        }
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("AutoPay Error: Gagal proses tagihan ID {$bill->id}. Pesan: " . $e->getMessage());
                    }
                }
            });
    }
}
