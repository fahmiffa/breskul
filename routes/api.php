<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Home;
use Illuminate\Support\Facades\Route;

Route::post('/webhook', [Home::class, 'midtransHook']);
Route::post('/push', [ApiController::class, 'rfid']);

Route::post('/status', function () {
    return response()->json([
        'status' => false,
        'message' => 'Mohon maaf, aplikasi sedang dalam perbaikan.\nSilahkan coba lagi secara berkala',
        'version' => '1.0.0'
    ], 200);
});

Route::post('/payment', [ApiController::class, 'paymentWebhook']);
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
    Route::post('/scan-qr', [ApiController::class, 'scanQr']);
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
