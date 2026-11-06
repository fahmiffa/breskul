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
        // Ambil semua tagihan yang belum lunas (status = 0) yang memiliki pembayaran dengan konfigurasi jatuh tempo
        // Gunakan chunk untuk memproses data dalam batch
        Bill::with(['payment', 'head.murid.saldo', 'head.murid.users'])
            ->where('status', 0)
            ->whereHas('payment', function ($query) {
                $query->whereNotNull('jatuh_tempo');
            })
            ->chunk(100, function ($bills) {
                $today = now()->startOfDay();

                foreach ($bills as $bill) {
                    try {
                        $payment = $bill->payment;
                        $head = $bill->head;

                        // Jika relasi data tidak lengkap, skip
                        if (!$payment || !$head || !$payment->jatuh_tempo) {
                            continue;
                        }

                        // Tentukan tanggal jatuh tempo efektif
                        if ($payment->auto_renew) {
                            // Untuk auto-renew (tagihan bulanan), ambil tanggal (hari) dari jatuh_tempo
                            // lalu terapkan pada bulan dan tahun tagihan dibuat
                            $billDate = $bill->created_at ?? now();
                            $targetDay = (int) \Carbon\Carbon::parse($payment->jatuh_tempo)->day;
                            $daysInMonth = (int) $billDate->daysInMonth;
                            $effectiveDay = min($targetDay, $daysInMonth);

                            $dueDate = \Carbon\Carbon::create(
                                $billDate->year,
                                $billDate->month,
                                $effectiveDay
                            )->startOfDay();
                        } else {
                            // Untuk pembayaran non-auto-renew, gunakan tanggal jatuh tempo spesifik
                            $dueDate = \Carbon\Carbon::parse($payment->jatuh_tempo)->startOfDay();
                        }

                        // Hanya proses jika HARI INI sudah MELEWATI jatuh tempo
                        if ($today->lte($dueDate)) {
                            continue;
                        }

                        DB::beginTransaction();

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

                            \App\Models\Kas::create([
                                'tipe'       => 'pemasukan',
                                'nominal'    => $nominalTagihan,
                                'keterangan' => 'Pembayaran otomatis tagihan: ' . ($payment->name ?? 'Tagihan') . ' (Siswa: ' . $student->name . ')',
                                'kategori'   => 'Pembayaran Otomatis',
                                'tanggal'    => now()->toDateString(),
                                'app'        => $student->app ?? null,
                                'user_id'    => $student->users ? $student->users->id : null,
                                'referensi'  => 'Bill ID: ' . $bill->id,
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
