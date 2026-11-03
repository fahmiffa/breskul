<?php

use App\Http\Controllers\AnnoucementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Home;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('/informasi/{id}', function ($id) {
    return $id;
});

Route::get('/kebijakan-privasi', function () {
    return view('policy');
});

Route::prefix('panduan')->group(function () {
    Route::get('akun', function () {
        return view('akun');
    });

    Route::get('absensi', function () {
        return view('absensi');
    });
});

Route::get('/clear', function () {
    Artisan::call('optimize:clear');
        File::put(storage_path('logs/laravel.log'), '');
        return 'Log cleared';
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
    Route::get('/panduan', [Home::class, 'panduan'])->name('panduan');
    Route::get('/pembayaran', [Home::class, 'pembayaran'])->name('pay');
    Route::post('/pembayaran', [Home::class, 'assignPay']);
    Route::post('/pembayaran/verifikasi', [Home::class, 'manualVerify'])->name('pay.verify');
    Route::resource('saldo', App\Http\Controllers\SaldoController::class);
    Route::get('/topup', [App\Http\Controllers\TopupController::class, 'index'])->name('topup.index');
    Route::post('/topup/verifikasi', [App\Http\Controllers\TopupController::class, 'verify'])->name('topup.verify');
    Route::get('/kas', [App\Http\Controllers\KasController::class, 'index'])->name('kas.index');
    Route::post('/kas', [App\Http\Controllers\KasController::class, 'store'])->name('kas.store');
    Route::put('/kas/{id}', [App\Http\Controllers\KasController::class, 'update'])->name('kas.update');
    Route::delete('/kas/{id}', [App\Http\Controllers\KasController::class, 'destroy'])->name('kas.destroy');
    Route::get('/absensi', [Home::class, 'absensi'])->name('absensi');
    Route::get('/absensi/export', [Home::class, 'exportAbsensiExcel'])->name('absensi.export');
    Route::get('setting', [Home::class, 'setting'])->name('setting');
    Route::post('/pass', [Home::class, 'pass'])->name('pass');
    Route::resource('pengumuman', AnnoucementController::class);
    Route::resource('ekstrakurikuler', App\Http\Controllers\StudentExtracurricularController::class);
    Route::get('penjadwalan-ujian/{id}/pdf', [App\Http\Controllers\UjianAssignmentController::class, 'pdf'])->name('penjadwalan-ujian.pdf');
    Route::get('penjadwalan-ujian/bulk-verify', function () {
        return redirect()->route('dashboard.penjadwalan-ujian.index');
    });
    Route::post('penjadwalan-ujian/bulk-verify', [App\Http\Controllers\UjianAssignmentController::class, 'bulkVerify'])->name('penjadwalan-ujian.bulk-verify');
    Route::resource('penjadwalan-ujian', App\Http\Controllers\UjianAssignmentController::class);

    Route::get('job-progress/{jobId}', function ($jobId) {
        $progress = Cache::get("job-progress-{$jobId}", 0);
        $status = Cache::get("job-status-{$jobId}", null);
        $error = Cache::get("job-error-{$jobId}", null);
        $result = Cache::get("job-result-{$jobId}", null);
        return response()->json([
            'progress' => (int) $progress,
            'status' => $status,
            'error' => $error,
            'result' => $result,
        ]);
    });
    // === GNUCASH ACCOUNTING MODULE ===
    Route::prefix('gnucash')->name('gnucash.')->group(function () {
        // Accounts (Chart of Accounts)
        Route::get('accounts', [App\Http\Controllers\GnucashAccountController::class, 'index'])->name('accounts.index');
        Route::get('accounts/create', [App\Http\Controllers\GnucashAccountController::class, 'create'])->name('accounts.create');
        Route::post('accounts', [App\Http\Controllers\GnucashAccountController::class, 'store'])->name('accounts.store');
        Route::get('accounts/{id}/edit', [App\Http\Controllers\GnucashAccountController::class, 'edit'])->name('accounts.edit');
        Route::put('accounts/{id}', [App\Http\Controllers\GnucashAccountController::class, 'update'])->name('accounts.update');
        Route::delete('accounts/{id}', [App\Http\Controllers\GnucashAccountController::class, 'destroy'])->name('accounts.destroy');
        Route::get('accounts/{id}/ledger', [App\Http\Controllers\GnucashAccountController::class, 'ledger'])->name('accounts.ledger');
        Route::post('accounts/{id}/quick-transaction', [App\Http\Controllers\GnucashAccountController::class, 'quickTransaction'])->name('accounts.quick-transaction');

        // Transactions (Journal)
        Route::get('transactions/import', [App\Http\Controllers\GnucashTransactionController::class, 'importForm'])->name('transactions.import.form');
        Route::post('transactions/import', [App\Http\Controllers\GnucashTransactionController::class, 'import'])->name('transactions.import');
        Route::resource('transactions', App\Http\Controllers\GnucashTransactionController::class);
    });

    Route::prefix('master')->name('master.')->middleware('checkMaster')->group(function () {
        Route::resource('kelas', App\Http\Controllers\ClassesController::class);
        Route::get('/akun', [Home::class, 'akun'])->name('akun.index');
        Route::get('/akun/export', [Home::class, 'exportAkun'])->name('akun.export');
        Route::post('/akun/password', [Home::class, 'updateAccountPassword'])->name('akun.password');
        Route::post('/akun/status', [Home::class, 'updateAccountStatus'])->name('akun.status');
        Route::post('/akun/lock', [Home::class, 'updateAccountLock'])->name('akun.lock');
        Route::get('murid/template', [App\Http\Controllers\StudentsController::class, 'template'])->name('murid.template');
        Route::resource('murid', App\Http\Controllers\StudentsController::class);
        Route::get('murid/{id}/qrcode', [App\Http\Controllers\StudentsController::class, 'qrcode'])->name('murid.qrcode');
        Route::get('murid/{id}/qrcode/download', [App\Http\Controllers\StudentsController::class, 'downloadQrcode'])->name('murid.qrcode.download');
        Route::post('rfid/{rfid}', [App\Http\Controllers\StudentsController::class, 'storeRfid'])->name('rfid');
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
        Route::get('halaqah/students-by-class', [App\Http\Controllers\HalaqahController::class, 'getStudentsByClass'])->name('halaqah.students-by-class');
        Route::resource('halaqah', App\Http\Controllers\HalaqahController::class);
        Route::resource('absensi', App\Http\Controllers\AttendanceConfigController::class);
        Route::resource('izin', App\Http\Controllers\PermitController::class);
        Route::post('karyawan/import', [App\Http\Controllers\EmployeeController::class, 'import'])->name('karyawan.import');
        Route::get('karyawan/template', [App\Http\Controllers\EmployeeController::class, 'template'])->name('karyawan.template');
        Route::resource('karyawan', App\Http\Controllers\EmployeeController::class);
        Route::resource('ekstrakurikuler', App\Http\Controllers\ExtracurricularController::class);
        Route::post('soal/import', [App\Http\Controllers\SoalController::class, 'import'])->name('soal.import');
        Route::get('soal/template', [App\Http\Controllers\SoalController::class, 'template'])->name('soal.template');
        Route::resource('soal', App\Http\Controllers\SoalController::class);
        Route::resource('ujian', App\Http\Controllers\UjianController::class);
        Route::resource('bank', App\Http\Controllers\BankController::class);
        Route::get('perangkat/data', [App\Http\Controllers\DeviceController::class, 'data'])->name('perangkat.data');
        Route::resource('perangkat', App\Http\Controllers\DeviceController::class);
        Route::middleware(['isRole'])->group(function () {
            Route::resource('app', App\Http\Controllers\AppController::class);
            Route::resource('api', App\Http\Controllers\ApiKeyController::class);
            Route::resource('jabatan', App\Http\Controllers\JabatanController::class);
        });
        Route::get('/', [Home::class, 'index'])->name('index');
    });
});
