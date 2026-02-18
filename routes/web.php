<?php

use App\Http\Controllers\AnnoucementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Home;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('/kebijakan-privasi', function () {
    return view('policy');
});

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'loginForm']);
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/pengumuman', [AuthController::class, 'pengumuman']);
    Route::get('/pengumuman/{id}', [AuthController::class, 'pengumuman'])->name('pengumuman');
});

Route::prefix('dashboard')->middleware('auth')->name('dashboard.')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [Home::class, 'index'])->name('home');
    Route::get('/pembayaran', [Home::class, 'pembayaran'])->name('pay');
    Route::post('/pembayaran', [Home::class, 'assignPay']);
    Route::post('/pembayaran/verifikasi', [Home::class, 'manualVerify'])->name('pay.verify');
    Route::get('/absensi', [Home::class, 'absensi'])->name('absensi');
    Route::get('setting', [Home::class, 'setting'])->name('setting');
    Route::post('/pass', [Home::class, 'pass'])->name('pass');
    Route::resource('pengumuman', AnnoucementController::class);
    Route::resource('ekstrakurikuler', App\Http\Controllers\StudentExtracurricularController::class);
    Route::resource('penjadwalan-ujian', App\Http\Controllers\UjianAssignmentController::class);

    Route::get('job-progress/{jobId}', function ($jobId) {
        $progress = Cache::get("job-progress-{$jobId}", 0);
        return response()->json(['progress' => $progress]);
    });

    Route::prefix('master')->name('master.')->middleware('checkMaster')->group(function () {
        Route::resource('kelas', App\Http\Controllers\ClassesController::class);
        Route::get('/akun', [Home::class, 'akun'])->name('akun.index');
        Route::post('/akun/password', [Home::class, 'updateAccountPassword'])->name('akun.password');
        Route::post('/akun/status', [Home::class, 'updateAccountStatus'])->name('akun.status');
        Route::resource('murid', App\Http\Controllers\StudentsController::class);
        Route::get('murid/{id}/qrcode', [App\Http\Controllers\StudentsController::class, 'qrcode'])->name('murid.qrcode');
        Route::get('murid/{id}/qrcode/download', [App\Http\Controllers\StudentsController::class, 'downloadQrcode'])->name('murid.qrcode.download');
        Route::post('uuid/{rfid}', [App\Http\Controllers\StudentsController::class, 'storeRfid'])->name('rfid');
        Route::post('import', [App\Http\Controllers\StudentsController::class, 'import'])->name('import');
        Route::resource('guru', App\Http\Controllers\TeachController::class);
        Route::resource('semester', App\Http\Controllers\AcademicYearsController::class);
        Route::get('akademik', [App\Http\Controllers\AcademicYearsController::class, 'akademik'])->name('akademik.index');
        Route::post('/akademik/import', [App\Http\Controllers\AcademicYearsController::class, 'import'])->name('akademik.import');
        Route::post('akademik/assign-class', [App\Http\Controllers\AcademicYearsController::class, 'assignClass'])->name('akademik.assign-class');
        Route::resource('pembayaran', App\Http\Controllers\PaymentController::class);
        Route::resource('fakultas', App\Http\Controllers\FakultasController::class);
        Route::resource('prodi', App\Http\Controllers\ProdiController::class);
        Route::resource('mapel', App\Http\Controllers\MapelController::class);
        Route::resource('jadwal', App\Http\Controllers\MapelDayController::class);
        Route::resource('absensi', App\Http\Controllers\AttendanceConfigController::class);
        Route::resource('ekstrakurikuler', App\Http\Controllers\ExtracurricularController::class);
        Route::resource('soal', App\Http\Controllers\SoalController::class);
        Route::resource('ujian', App\Http\Controllers\UjianController::class);
        Route::middleware(['isRole'])->group(function () {
            Route::resource('app', App\Http\Controllers\AppController::class);
            Route::resource('api', App\Http\Controllers\ApiKeyController::class);
        });
        Route::get('/', [Home::class, 'index'])->name('index');
    });
});
