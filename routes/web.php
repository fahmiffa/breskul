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

Route::prefix('dashboard')->middleware(['auth', 'app.context'])->name('dashboard.')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [Home::class, 'index'])->name('home');
    Route::get('/panduan', [Home::class, 'panduan'])->name('panduan');

    // === Pembayaran (dengan permission) ===
    Route::middleware('permission:menu.pembayaran')->group(function () {
        Route::get('/pembayaran', [Home::class, 'pembayaran'])->name('pay');
        Route::post('/pembayaran', [Home::class, 'assignPay']);
        Route::post('/pembayaran/verifikasi', [Home::class, 'manualVerify'])->name('pay.verify');
    });

    // === Saldo (dengan permission) ===
    Route::middleware('permission:menu.saldo')->group(function () {
        Route::resource('saldo', App\Http\Controllers\SaldoController::class);
    });

    // === Topup (dengan permission) ===
    Route::middleware('permission:menu.topup')->group(function () {
        Route::get('/topup', [App\Http\Controllers\TopupController::class, 'index'])->name('topup.index');
        Route::post('/topup/verifikasi', [App\Http\Controllers\TopupController::class, 'verify'])->name('topup.verify');
    });

    // === Kas (dengan permission) ===
    Route::middleware('permission:menu.kas')->group(function () {
        Route::get('/kas', [App\Http\Controllers\KasController::class, 'index'])->name('kas.index');
        Route::post('/kas', [App\Http\Controllers\KasController::class, 'store'])->name('kas.store');
        Route::put('/kas/{id}', [App\Http\Controllers\KasController::class, 'update'])->name('kas.update');
        Route::delete('/kas/{id}', [App\Http\Controllers\KasController::class, 'destroy'])->name('kas.destroy');
    });

    // === Absensi (dengan permission) ===
    Route::middleware('permission:menu.absensi')->group(function () {
        Route::get('/absensi', [Home::class, 'absensi'])->name('absensi');
        Route::get('/absensi/export', [Home::class, 'exportAbsensiExcel'])->name('absensi.export');
    });

    Route::get('setting', [Home::class, 'setting'])->name('setting');
    Route::post('/pass', [Home::class, 'pass'])->name('pass');

    // === Pengumuman (dengan permission) ===
    Route::middleware('permission:menu.pengumuman')->group(function () {
        Route::resource('pengumuman', AnnoucementController::class);
    });

    Route::resource('ekstrakurikuler', App\Http\Controllers\StudentExtracurricularController::class);

    // === Penjadwalan Ujian (dengan permission) ===
    Route::middleware('permission:menu.penjadwalan')->group(function () {
        Route::get('penjadwalan-ujian/{id}/pdf', [App\Http\Controllers\UjianAssignmentController::class, 'pdf'])->name('penjadwalan-ujian.pdf');
        Route::get('penjadwalan-ujian/bulk-verify', function () {
            return redirect()->route('dashboard.penjadwalan-ujian.index');
        });
        Route::post('penjadwalan-ujian/bulk-verify', [App\Http\Controllers\UjianAssignmentController::class, 'bulkVerify'])->name('penjadwalan-ujian.bulk-verify');
        Route::resource('penjadwalan-ujian', App\Http\Controllers\UjianAssignmentController::class);
    });

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

    // === GNUCASH ACCOUNTING MODULE (dengan permission) ===
    Route::prefix('gnucash')->name('gnucash.')->middleware('permission:menu.jurnal')->group(function () {
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

        // Reports (Laporan Transaksi Berdasarkan Akun)
        Route::get('reports', [App\Http\Controllers\GnucashReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [App\Http\Controllers\GnucashReportController::class, 'export'])->name('reports.export');
        Route::get('laporan', [App\Http\Controllers\GnucashReportController::class, 'index'])->name('laporan.index');
        Route::get('laporan/export', [App\Http\Controllers\GnucashReportController::class, 'export'])->name('laporan.export');
    });

    // === MASTER GROUP ===
    Route::prefix('master')->name('master.')->group(function () {
        // --- Kelas (permission) ---
        Route::middleware('permission:menu.kelas')->group(function () {
            Route::resource('kelas', App\Http\Controllers\ClassesController::class);
        });

        // --- Akun (permission) ---
        Route::middleware('permission:menu.akun')->group(function () {
            Route::get('/akun', [Home::class, 'akun'])->name('akun.index');
            Route::get('/akun/export', [Home::class, 'exportAkun'])->name('akun.export');
            Route::post('/akun/password', [Home::class, 'updateAccountPassword'])->name('akun.password');
            Route::post('/akun/status', [Home::class, 'updateAccountStatus'])->name('akun.status');
            Route::post('/akun/lock', [Home::class, 'updateAccountLock'])->name('akun.lock');
        });

        // --- Murid (permission) ---
        Route::middleware('permission:menu.murid')->group(function () {
            Route::get('murid/template', [App\Http\Controllers\StudentsController::class, 'template'])->name('murid.template');
            Route::resource('murid', App\Http\Controllers\StudentsController::class);
            Route::get('murid/{id}/qrcode', [App\Http\Controllers\StudentsController::class, 'qrcode'])->name('murid.qrcode');
            Route::get('murid/{id}/qrcode/download', [App\Http\Controllers\StudentsController::class, 'downloadQrcode'])->name('murid.qrcode.download');
            Route::post('rfid/{rfid}', [App\Http\Controllers\StudentsController::class, 'storeRfid'])->name('rfid');
            Route::post('import', [App\Http\Controllers\StudentsController::class, 'import'])->name('import');
        });

        // --- Guru (permission) ---
        Route::middleware('permission:menu.guru')->group(function () {
            Route::resource('guru', App\Http\Controllers\TeachController::class);
        });

        // --- Semester (permission) ---
        Route::middleware('permission:menu.semester')->group(function () {
            Route::resource('semester', App\Http\Controllers\AcademicYearsController::class);
        });

        // --- Akademik (permission) ---
        Route::middleware('permission:menu.akademik')->group(function () {
            Route::get('akademik', [App\Http\Controllers\AcademicYearsController::class, 'akademik'])->name('akademik.index');
            Route::post('/akademik/import', [App\Http\Controllers\AcademicYearsController::class, 'import'])->name('akademik.import');
            Route::post('akademik/assign-class', [App\Http\Controllers\AcademicYearsController::class, 'assignClass'])->name('akademik.assign-class');
        });

        // --- Pembayaran Master (permission) ---
        Route::middleware('permission:menu.master.pembayaran')->group(function () {
            Route::resource('pembayaran', App\Http\Controllers\PaymentController::class);
        });

        // --- Fakultas (permission) ---
        Route::middleware('permission:menu.fakultas')->group(function () {
            Route::resource('fakultas', App\Http\Controllers\FakultasController::class);
        });

        // --- Prodi (permission) ---
        Route::middleware('permission:menu.prodi')->group(function () {
            Route::resource('prodi', App\Http\Controllers\ProdiController::class);
        });

        // --- Mapel (permission) ---
        Route::middleware('permission:menu.mapel')->group(function () {
            Route::resource('mapel', App\Http\Controllers\MapelController::class);
        });

        // --- Jadwal (permission) ---
        Route::middleware('permission:menu.jadwal')->group(function () {
            Route::resource('jadwal', App\Http\Controllers\MapelDayController::class);
        });

        // --- Halaqah (permission) ---
        Route::middleware('permission:menu.halaqah')->group(function () {
            Route::get('halaqah/students-by-class', [App\Http\Controllers\HalaqahController::class, 'getStudentsByClass'])->name('halaqah.students-by-class');
            Route::resource('halaqah', App\Http\Controllers\HalaqahController::class);
        });

        // --- Absensi Setting (permission) ---
        Route::middleware('permission:menu.absensi.setting')->group(function () {
            Route::resource('absensi', App\Http\Controllers\AttendanceConfigController::class);
        });

        // --- Izin (permission) ---
        Route::middleware('permission:menu.izin')->group(function () {
            Route::resource('izin', App\Http\Controllers\PermitController::class);
        });

        // --- Karyawan (permission) ---
        Route::middleware('permission:menu.karyawan')->group(function () {
            Route::get('karyawan/export', [App\Http\Controllers\EmployeeController::class, 'export'])->name('karyawan.export');
            Route::post('karyawan/import', [App\Http\Controllers\EmployeeController::class, 'import'])->name('karyawan.import');
            Route::get('karyawan/template', [App\Http\Controllers\EmployeeController::class, 'template'])->name('karyawan.template');
            Route::resource('karyawan', App\Http\Controllers\EmployeeController::class);
        });

        // --- Ekstrakurikuler (permission) ---
        Route::middleware('permission:menu.ekstrakurikuler')->group(function () {
            Route::resource('ekstrakurikuler', App\Http\Controllers\ExtracurricularController::class);
        });

        // --- Soal (permission) ---
        Route::middleware('permission:menu.soal')->group(function () {
            Route::post('soal/import', [App\Http\Controllers\SoalController::class, 'import'])->name('soal.import');
            Route::get('soal/template', [App\Http\Controllers\SoalController::class, 'template'])->name('soal.template');
            Route::resource('soal', App\Http\Controllers\SoalController::class);
        });

        // --- Ujian (permission) ---
        Route::middleware('permission:menu.ujian')->group(function () {
            Route::resource('ujian', App\Http\Controllers\UjianController::class);
        });

        // --- Bank (permission) ---
        Route::middleware('permission:menu.bank')->group(function () {
            Route::resource('bank', App\Http\Controllers\BankController::class);
        });

        // --- Perangkat (permission) ---
        Route::middleware('permission:menu.perangkat')->group(function () {
            Route::get('perangkat/data', [App\Http\Controllers\DeviceController::class, 'data'])->name('perangkat.data');
            Route::resource('perangkat', App\Http\Controllers\DeviceController::class);
        });

        // --- Super Admin Only (permission) ---
        Route::middleware('permission:menu.app')->group(function () {
            Route::resource('app', App\Http\Controllers\AppController::class);
        });
        Route::middleware('permission:menu.api')->group(function () {
            Route::resource('api', App\Http\Controllers\ApiKeyController::class);
        });
        Route::middleware('permission:menu.jabatan')->group(function () {
            Route::resource('jabatan', App\Http\Controllers\JabatanController::class);
        });
        Route::middleware('permission:menu.fitur')->group(function () {
            Route::resource('fitur', App\Http\Controllers\FeatureController::class);
            Route::resource('feature', App\Http\Controllers\FeatureController::class)->names('feature');
            Route::get('role-permission', [App\Http\Controllers\RolePermissionController::class, 'index'])->name('role-permission.index');
            Route::post('role-permission/toggle', [App\Http\Controllers\RolePermissionController::class, 'toggle'])->name('role-permission.toggle');
            Route::post('role-permission/{role}', [App\Http\Controllers\RolePermissionController::class, 'updateRolePermissions'])->name('role-permission.update');
        });

        Route::get('/', [Home::class, 'index'])->name('index');
    });
});
