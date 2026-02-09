<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Head;
use App\Models\Present;
use App\Models\Students;
use App\Models\User;
use App\Services\BillingCodeService;
use App\Services\Midtrans\Transaction;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Jobs\ProcessFcm;
use App\Models\Classes;
use App\Models\Mapel;
use App\Models\Teach;
use App\Models\Extracurricular;

class Home extends Controller
{

    public function midtransPay(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ], [
            'required' => ':attribute wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 400);
        }

        try {

            $kode       = 'trxb-' . date("YmdHis");
            $order      = Bill::where('id', $request->id)->first();
            $order->mid = $kode;
            $order->save();

            $total = $order->payment->nominal;
            $name  = $order->head->murid->name;
            $email = $order->head->murid->users->email ? $order->head->murid->users->email : "qlabcode@mail.com";
            $hp    = $order->head->murid->users->nomor ? $order->head->murid->users->nomor : "085640431181";
            $mid   = $order->mid;
            $des   = "Tagihan " . $order->payment->name;

            $params = [
                'transaction_details' => [
                    'order_id'     => $mid,
                    'gross_amount' => $total,
                ],
                'customer_details'    => [
                    'first_name' => $name,
                    'last_name'  => $name,
                    'email'      => $email,
                    'phone'      => $hp,
                ],
            ];

            $response = Http::post(env('NODE_GATEWAY_URL') . '/pay/create', $params);
            if ($response->failed()) {
                return response()->json(['error' => 'Failed to create transaction'], 500);
            }

            return $response->json();
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal membuat transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function midtransHook(Request $request)
    {
        Log::info('Midtrans Webhook Received:', $request->all());
        $data = $request->all();
        return Transaction::hook($data);
    }

    public function absensi(Request $request)
    {
        $start = $request->get('start_date', date('Y-m-d'));
        $end   = $request->get('end_date', date('Y-m-d'));

        $items = Present::query()
            ->whereDate('waktu', '>=', $start)
            ->whereDate('waktu', '<=', $end)
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })
            ->with('murid')
            ->latest()
            ->get();

        return view('home.present.index', compact('items', 'start', 'end'));
    }

    public function akun()
    {
        $items = User::latest()
            ->whereIn('role', [2, 3])
            ->where(function ($q) {
                $q->whereHas('teacherData', function ($q) {
                    $q->where('app', auth()->user()->app->id);
                })->orWhereHas('studentData', function ($q) {
                    $q->where('app', auth()->user()->app->id);
                });
            })
            ->with(['teacherData', 'studentData'])
            ->get();

        return view('master.akun.index', compact('items'));
    }

    public function index(Request $request)
    {
        $year = $request->get('year', date('Y'));

        // Data Grafik Pembayaran
        $bills = Bill::where('status', 1)
            ->whereYear('updated_at', $year)
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->whereHas('head.murid', function ($q) {
                    $q->where('app', auth()->user()->app->id);
                });
            })
            ->with('payment')
            ->get()
            ->groupBy(function ($item) {
                return $item->updated_at->format('n');
            });

        $paymentData = [];
        for ($i = 1; $i <= 12; $i++) {
            $paymentData[] = $bills->has($i) ? $bills[$i]->sum(function ($b) {
                return $b->payment->nominal ?? 0;
            }) : 0;
        }

        // Data Grafik Tagihan (Belum Lunas)
        $unpaidBills = Bill::where('status', 0)
            ->whereYear('created_at', $year)
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->whereHas('head.murid', function ($q) {
                    $q->where('app', auth()->user()->app->id);
                });
            })
            ->with('payment')
            ->get()
            ->groupBy(function ($item) {
                return $item->created_at->format('n');
            });

        $unpaidPaymentData = [];
        for ($i = 1; $i <= 12; $i++) {
            $unpaidPaymentData[] = $unpaidBills->has($i) ? $unpaidBills[$i]->sum(function ($b) {
                return $b->payment->nominal ?? 0;
            }) : 0;
        }

        // Data Grafik Absensi (Bulan Ini)
        $presents = Present::whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('m'))
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })
            ->get()
            ->groupBy(function ($item) {
                return $item->created_at->format('j');
            });

        $daysInMonth = date('t');
        $attendanceData = [];
        $attendanceLabels = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $attendanceLabels[] = $i;
            $attendanceData[] = $presents->has($i) ? $presents[$i]->count() : 0;
        }

        // Summary Counts
        $appId = auth()->user()->app->id ?? null;
        $isAppUser = auth()->user()->role == 1 && $appId;

        $totalMurid = Students::when($isAppUser, function ($q) use ($appId) {
            $q->where('app', $appId);
        })->count();
        $totalGuru = Teach::when($isAppUser, function ($q) use ($appId) {
            $q->where('app', $appId);
        })->count();
        $totalKelas = Classes::when($isAppUser, function ($q) use ($appId) {
            $q->where('app', $appId);
        })->count();
        $totalMapel = Mapel::when($isAppUser, function ($q) use ($appId) {
            $q->where('app', $appId);
        })->count();
        $totalEkskul = Extracurricular::when($isAppUser, function ($q) use ($appId) {
            $q->where('app', $appId);
        })->count();

        $totalProdi = \App\Models\Prodi::when($isAppUser, function ($q) use ($appId) {
            $q->where('app', $appId);
        })->count();

        return view('home.index', compact('paymentData', 'unpaidPaymentData', 'attendanceData', 'attendanceLabels', 'year', 'totalMurid', 'totalGuru', 'totalKelas', 'totalMapel', 'totalEkskul', 'totalProdi'));
    }

    public function pembayaran()
    {
        $title = "Pembayaran";
        $appId = auth()->user()->app->id ?? null;
        $isAppUser = auth()->user()->role == 1 && $appId;

        if (config('app.school_mode')) {
            $classes = Classes::when($isAppUser, function ($q) use ($appId) {
                $q->where('app', $appId);
            })->get();
        } else {
            $classes = \App\Models\Prodi::when($isAppUser, function ($q) use ($appId) {
                $q->where('app', $appId);
            })->get();
        }

        $items = Students::latest()
            ->when($isAppUser, function ($query) use ($appId) {
                $query->where('app', $appId);
            })
            ->has('reg')
            ->get()
            ->map(function ($q) {

                $bills = $q->reg->bill->map(function ($bill) {
                    return [
                        'bill'    => $bill->id ?? null,
                        'status'  => $bill->state ?? null,
                        'via'     => $bill->via ?? null,
                        'name'    => $bill->payment->name ?? null,
                        'nominal' => number_format($bill->payment->nominal, 0, ',', '.') ?? null,
                    ];
                });

                return [
                    'id'    => $q->id,
                    'head'  => $q->reg->id,
                    'nis'   => $q->nis,
                    'name'  => $q->name,
                    'kelas' => config('app.school_mode')
                        ? ($q->reg->kelas->name ?? null)
                        : ($q->reg->prodi->name ?? null),
                    'bill'  => $bills,
                ];
            });
        return view('home.pay.index', compact('items', 'title', 'classes'));
    }

    public function assignPay(Request $request)
    {
        DB::beginTransaction();

        try {
            $numb = 0;

            foreach ($request->student_ids as $studentId) {

                $head = Bill::where('head_id', $studentId)
                    ->where('payment_id', $request->class_id)
                    ->first();

                if (! $head) {
                    $bill = new Bill;
                    $bill->payment_id = $request->class_id;
                    $bill->head_id    = $studentId;
                    $bill->save();
                    $numb++;

                    $message = [
                        'topic' => 'user_' . $bill->head->murid->users->id,
                        'title' => 'Pembayaran',
                        'body'  => 'Anda mempunyai pembayaran ' . $bill->payment->name,
                    ];
                    ProcessFcm::dispatch($message);
                }
            }

            DB::commit();

            if ($numb > 0) {
                $studentTerm = config('app.school_mode') ? 'murid' : 'mahasiswa';
                return response()->json([
                    'message' => "Pembayaran berhasil ditambahkan untuk {$numb} $studentTerm"
                ]);
            }

            return response()->json([
                'message' => 'Pembayaran tidak valid'
            ], 400);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('assignPay error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
    }

    public function manualVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:bills,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $bill = Bill::find($request->id);

            if ($bill->status == 1) {
                return response()->json([
                    'message' => 'Tagihan sudah diverifikasi sebelumnya'
                ], 400);
            }

            $bill->status = 1;
            $bill->save();

            // Send FCM Notification
            if ($bill->head && $bill->head->murid && $bill->head->murid->users) {
                $message = [
                    'topic' => 'user_' . $bill->head->murid->users->id,
                    'title' => 'Pembayaran Berhasil',
                    'body'  => 'Tagihan ' . ($bill->payment->name ?? 'Pembayaran') . ' telah dikonfirmasi oleh Admin.',
                ];
                ProcessFcm::dispatch($message);
            }

            DB::commit();

            return response()->json([
                'message' => 'Pembayaran berhasil diverifikasi.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('manualVerify error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat verifikasi'
            ], 500);
        }
    }


    public function setting()
    {
        return view('setting');
    }

    public function pass(Request $request)
    {

        $request->validate([
            'current_password'      => 'required',
            'new_password'          => 'required|min:8|same:password_confirmation',
            'password_confirmation' => 'required',
        ], [
            'current_password.required'      => 'Password sekarang wajib diisi.',
            'new_password.required'          => 'Password baru wajib diisi.',
            'new_password.min'               => 'Password baru minimal 8 karakter.',
            'new_password.same'              => 'Konfirmasi password tidak cocok.',
            'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
        ]);

        if (! Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'Password Sekarang salah']);
        }

        Auth::user()->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
    public function updateAccountPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'       => 'required|exists:users,id',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 400);
        }

        try {
            $user           = User::find($request->id);
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'message' => 'Password berhasil diperbarui',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateAccountStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'     => 'required|exists:users,id',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 400);
        }

        try {
            $user         = User::find($request->id);
            $user->status = $request->status;
            $user->save();

            return response()->json([
                'message' => 'Status berhasil diperbarui',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
