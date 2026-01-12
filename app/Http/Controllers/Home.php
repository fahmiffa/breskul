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

    public function absensi()
    {
        $items = Present::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })
            ->with('murid')
            ->latest()
            ->get();
        return view('home.present.index', compact('items'));
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

    public function index(BillingCodeService $service)
    {
        // $code = $service->generateUniqueCode();
        // dd($code);
        return view('home.index');
    }

    public function pembayaran()
    {
        $title = "Pembayaran";
        $items = Students::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
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
                    'kelas' => $q->reg->kelas->name ?? null,
                    'bill'  => $bills,
                ];
            });
        return view('home.pay.index', compact('items', 'title'));
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

                    // 🔥 AMBIL TOKEN STRING (BUKAN optional)
                    $fcm = $bill->head->murid->users->fcm ?? null;

                    if (! empty($fcm)) {

                        $message = [
                            'message' => [
                                'token' => $fcm, // STRING
                                'notification' => [
                                    'title' => 'Pembayaran',
                                    'body'  => 'Anda mempunyai pembayaran ' . $bill->payment->name,
                                ],
                            ],
                        ];

                        ProcessFcm::dispatch($message);
                    } else {
                        Log::warning('FCM token kosong', [
                            'student_id' => $studentId,
                        ]);
                    }
                }
            }

            DB::commit();

            if ($numb > 0) {
                return response()->json([
                    'message' => "Pembayaran berhasil ditambahkan untuk {$numb} murid"
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

}
