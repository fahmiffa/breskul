<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\LogSaldo;
use App\Models\Prodi;
use App\Models\Saldo;
use App\Models\Students;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SaldoController extends Controller
{
    public function index(Request $request)
    {
        $title = "Manajemen Saldo";
        $appId = auth()->user()->app->id ?? null;
        $isAppUser = auth()->user()->role == 1 && $appId;

        // Ambil data kelas/prodi khusus yang memiliki murid pesantren untuk filter
        $pesantrenStudentIds = Students::where('boarding', true)
            ->when($isAppUser, function ($q) use ($appId) {
                $q->where('app', $appId);
            })->pluck('id');

        if (config('app.school_mode')) {
            $pesantrenClassIds = \App\Models\Head::whereIn('student_id', $pesantrenStudentIds)->pluck('class_id')->unique()->filter();
            $classes = Classes::whereIn('id', $pesantrenClassIds)
                ->when($isAppUser, function ($q) use ($appId) {
                    $q->where('app', $appId);
                })->get();
        } else {
            $pesantrenProdiIds = \App\Models\Head::whereIn('student_id', $pesantrenStudentIds)->pluck('prodi_id')->unique()->filter();
            $classes = Prodi::whereIn('id', $pesantrenProdiIds)
                ->when($isAppUser, function ($q) use ($appId) {
                    $q->where('app', $appId);
                })->get();
        }

        $query = Students::query()
            ->where('boarding', true)
            ->when($isAppUser, function ($query) use ($appId) {
                $query->where('app', $appId);
            })
            ->has('reg')
            ->with(['reg.kelas', 'reg.prodi', 'saldo']);

        // Search Filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        // Filter Kelas
        if ($request->filled('kelas')) {
            $kelas = $request->kelas;
            $query->whereHas('reg', function ($q) use ($kelas) {
                if (config('app.school_mode')) {
                    $q->whereHas('kelas', function ($k) use ($kelas) {
                        $k->where('name', $kelas)->orWhere('id', $kelas);
                    });
                } else {
                    $q->whereHas('prodi', function ($p) use ($kelas) {
                        $p->where('name', $kelas)->orWhere('id', $kelas);
                    });
                }
            });
        }

        $items = $query->latest('id')->paginate(15)->withQueryString();

        // Total saldo keseluruhan khusus murid pesantren
        $totalSaldo = Saldo::whereHas('student', function ($s) {
            $s->where('boarding', true);
        })->when($isAppUser, function ($q) use ($appId) {
            $q->where('app_id', $appId);
        })->sum('nominal');

        return view('home.saldo.index', compact('items', 'title', 'classes', 'totalSaldo'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'students_id' => 'required|exists:students,id',
            'tipe'        => 'required|in:kredit,debit',
            'nominal'     => 'required|numeric|min:1',
            'keterangan'  => 'nullable|string|max:255',
        ], [
            'students_id.required' => 'Pilih murid terlebih dahulu.',
            'students_id.exists'   => 'Data murid tidak valid.',
            'tipe.required'        => 'Tipe transaksi wajib dipilih.',
            'nominal.required'     => 'Nominal wajib diisi.',
            'nominal.min'          => 'Nominal minimal Rp 1.',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => $validator->errors()->first()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $student = Students::findOrFail($request->students_id);
            if (!$student->boarding) {
                DB::rollBack();
                $msg = 'Fitur saldo hanya diperuntukkan untuk murid dengan status pesantren.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['message' => $msg], 403);
                }
                return back()->with('err', $msg);
            }
            $appId = $student->app ?? (auth()->user()->app->id ?? null);

            // Temukan atau buat saldo
            $saldo = Saldo::firstOrCreate(
                ['students_id' => $student->id],
                [
                    'nominal' => 0,
                    'app_id'  => $appId,
                ]
            );

            $nominal = (float) $request->nominal;

            if ($request->tipe === 'debit') {
                if ($saldo->nominal < $nominal) {
                    DB::rollBack();
                    $msg = 'Saldo tidak mencukupi untuk melakukan penarikan/debit.';
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['message' => $msg], 400);
                    }
                    return back()->with('err', $msg);
                }
                $saldo->nominal -= $nominal;
            } else {
                $saldo->nominal += $nominal;
            }

            $saldo->save();

            // Catat ke log saldo
            LogSaldo::create([
                'saldo_id'    => $saldo->id,
                'students_id' => $student->id,
                'tipe'        => $request->tipe,
                'nominal'     => $nominal,
                'keterangan'  => $request->keterangan ?: ($request->tipe === 'kredit' ? 'Top up saldo' : 'Penarikan/pemotongan saldo'),
            ]);

            DB::commit();

            $message = 'Transaksi ' . ($request->tipe === 'kredit' ? 'Kredit (Top Up)' : 'Debit (Tarik)') . ' berhasil disimpan.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message'     => $message,
                    'new_saldo'   => $saldo->nominal,
                    'formatted'   => number_format($saldo->nominal, 0, ',', '.'),
                ]);
            }

            return back()->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
            }

            return back()->with('err', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $student = Students::with(['reg.kelas', 'reg.prodi', 'saldo'])->findOrFail($id);
        if (!$student->boarding) {
            return redirect()->route('dashboard.saldo.index')->with('err', 'Fitur saldo hanya diperuntukkan untuk murid dengan status pesantren.');
        }

        $logs = LogSaldo::where('students_id', $student->id)
            ->latest()
            ->paginate(20);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'student' => $student,
                'logs'    => $logs,
            ]);
        }

        $title = "Riwayat Saldo - " . $student->name;
        return view('home.saldo.show', compact('student', 'logs', 'title'));
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $student = Students::findOrFail($id);
            if (!$student->boarding) {
                DB::rollBack();
                return back()->with('err', 'Fitur saldo hanya diperuntukkan untuk murid dengan status pesantren.');
            }

            $saldo = Saldo::where('students_id', $id)->first();
            if ($saldo) {
                // Log reset
                if ($saldo->nominal > 0) {
                    LogSaldo::create([
                        'saldo_id'    => $saldo->id,
                        'students_id' => $id,
                        'tipe'        => 'debit',
                        'nominal'     => $saldo->nominal,
                        'keterangan'  => 'Reset saldo oleh admin',
                    ]);
                }
                $saldo->nominal = 0;
                $saldo->save();
            }

            DB::commit();
            return back()->with('success', 'Saldo berhasil direset ke 0.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('err', 'Gagal mereset saldo: ' . $e->getMessage());
        }
    }
}
