<?php
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Home;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::post('/webhook', [Home::class, 'midtransHook']);
Route::post('/push', [ApiController::class, 'rfid']);

Route::post('/status', function () {
    return response()->json([
        'status' => false,
    ], 200);
});

Route::post('/payment', function (Request $request) {
    Log::channel('payment')->info($request->all());
    return response()->json(['success' => true], 200);
});
Route::prefix('fire')->group(function () {
    Route::post('/refresh', [ApiController::class, 'refresh']);
    Route::post('/login', [ApiController::class, 'login']);
    Route::get('/topic', [ApiController::class, 'topic']);
    Route::post('/fcm', [ApiController::class, 'fcm']);
    Route::post('/forget', [ApiController::class, 'forget']);
});
Route::middleware('jwt')->group(function () {
    Route::post('/pass', [ApiController::class, 'upass']);
    Route::post('/pay', [Home::class, 'midtransPay']);
    Route::get('/absensi', [ApiController::class, 'absensi']);
    Route::get('/data', [ApiController::class, 'data']);
    Route::get('/jadwal', [ApiController::class, 'jadwal']);
    Route::get('/bill', [ApiController::class, 'bill']);
    Route::get('/pengumuman', [ApiController::class, 'pengumuman']);
    Route::get('/pengumuman/{id}', [ApiController::class, 'pengumuman']);
});
