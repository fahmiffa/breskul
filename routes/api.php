<?php
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Home;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Models\Bill;

Route::post('/webhook', [Home::class, 'midtransHook']);
Route::post('/push', [ApiController::class, 'rfid']);

Route::post('/status', function () {
    return response()->json([
        'status' => false,
    ], 200);
});

Route::post('/payment', function (Request $request) {
    Log::channel('payment')->info($request->all());

    $title = $request->input('title');
    $text = $request->input('text');
    $content = $title . ' ' . $text;

    // Pattern to extract amount like Rp200.740
    if (preg_match('/Rp([\d\.]+)/', $content, $matches)) {
        $amountStr = str_replace('.', '', $matches[1]); // 200740
        $uniqueCode = substr($amountStr, -3); // 740

        // Find bill with this unique code
        // We might want to check for today's pending bills to avoid collision, 
        // but for now following instructions strictly: "cek di table bill"
        $bill = Bill::where('unique_code', $uniqueCode)
                    ->where('status', 0)
                    ->latest()
                    ->first();

        if ($bill) {
            // Update status
            $bill->status = 1;
            $bill->save();

            // Send FCM
            // Bill -> Student -> User (via 'users' relation)
            if ($bill->student_id) {
                $student = \App\Models\Students::find($bill->student_id);
                if ($student && $student->users && $student->users->fcm) {
                    $fcmToken = $student->users->fcm;
                    
                    $message = [
                        "message" => [
                            "token" => $fcmToken,
                            "notification" => [
                                "title" => "Pembayaran Berhasil",
                                "body" => "Pembayaran sebesar Rp" . number_format($amountStr, 0, ',', '.') . " berhasil diterima.",
                            ],
                        ],
                    ];
                    
                    try {
                        \App\Jobs\ProcessFcm::dispatch($message);
                    } catch (\Exception $e) {
                        Log::error("Failed to dispatch FCM: " . $e->getMessage());
                    }
                }
            }
        }
    }

    return response()->json(['success' => true], 200);
});
Route::prefix('fire')->group(function () {
    Route::post('/refresh', [ApiController::class, 'refresh']);
    Route::post('/login', [ApiController::class, 'login']);
    Route::post('/fcm', [ApiController::class, 'fcm']);
    Route::post('/forget', [ApiController::class, 'forget']);
    });
    Route::middleware('jwt')->group(function () {
    Route::get('/topic', [ApiController::class, 'topic']);
    Route::post('/pass', [ApiController::class, 'upass']);
    Route::post('/pay', [Home::class, 'midtransPay']);
    Route::get('/absensi', [ApiController::class, 'absensi']);
    Route::get('/absensi/config', [ApiController::class, 'getAbsensiConfig']);
    Route::post('/absensi/submit', [ApiController::class, 'submitAbsensi']);
    Route::get('/data', [ApiController::class, 'data']);
    Route::get('/jadwal', [ApiController::class, 'jadwal']);
    Route::get('/ekstra', [ApiController::class, 'ekstra']);
    Route::post('/ekstra/daftar', [ApiController::class, 'daftarEkstra']);
    Route::get('/ekstrakurikuler', [ApiController::class, 'ekstrakurikuler']);
    Route::get('/bill', [ApiController::class, 'bill']);
    Route::post('/bill/generate', [ApiController::class, 'generateBillQris']);
    Route::post('/bill/pay-simulation', [ApiController::class, 'paySimulation']);
    Route::get('/pengumuman', [ApiController::class, 'pengumuman']);
    Route::get('/pengumuman/{id}', [ApiController::class, 'pengumuman']);
    Route::post('/upload-image', [ApiController::class, 'uploadImage']);
});
