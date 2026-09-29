<?php

namespace App\Http\Controllers;

use App\Models\Kas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class KasController extends Controller
{
    public function index(Request $request)
    {
        $title = "Kas Keuangan";
        $appId = auth()->user()->app->id ?? null;
        $isAppUser = auth()->user()->role == 1 && $appId;

        if ($request->ajax() || $request->wantsJson()) {
            $query = Kas::query()
                ->when($isAppUser, function ($q) use ($appId) {
                    $q->where('app', $appId);
                });

            // Search
            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('keterangan', 'like', "%{$search}%")
                      ->orWhere('kategori', 'like', "%{$search}%")
                      ->orWhere('referensi', 'like', "%{$search}%");
                });
            }

            // Filter tipe
            if ($request->filled('tipe') && in_array($request->tipe, ['pemasukan', 'pengeluaran'])) {
                $query->where('tipe', $request->tipe);
            }

            // Filter bulan
            if ($request->filled('bulan')) {
                $parts = explode('-', $request->bulan);
                if (count($parts) === 2) {
                    $query->whereYear('tanggal', $parts[0])
                          ->whereMonth('tanggal', $parts[1]);
                }
            }

            $perPage = (int) $request->get('per_page', 15);
            $paginated = $query->latest('tanggal')->latest('id')->paginate($perPage);

            // Summary
            $summaryQuery = Kas::query()
                ->when($isAppUser, fn($q) => $q->where('app', $appId));

            if ($request->filled('bulan')) {
                $parts = explode('-', $request->bulan);
                if (count($parts) === 2) {
                    $summaryQuery->whereYear('tanggal', $parts[0])->whereMonth('tanggal', $parts[1]);
                }
            }

            $totalPemasukan  = (clone $summaryQuery)->where('tipe', 'pemasukan')->sum('nominal');
            $totalPengeluaran = (clone $summaryQuery)->where('tipe', 'pengeluaran')->sum('nominal');

            $items = $paginated->getCollection()->map(function ($q) {
                return [
                    'id'          => $q->id,
                    'tipe'        => $q->tipe,
                    'nominal'     => $q->nominal,
                    'keterangan'  => $q->keterangan,
                    'kategori'    => $q->kategori ?? '-',
                    'tanggal'     => $q->tanggal->format('Y-m-d'),
                    'referensi'   => $q->referensi ?? '-',
                    'created_at'  => $q->created_at->format('Y-m-d H:i'),
                ];
            });

            return response()->json([
                'data'              => $items,
                'current_page'      => $paginated->currentPage(),
                'last_page'         => $paginated->lastPage(),
                'per_page'          => $paginated->perPage(),
                'total'             => $paginated->total(),
                'from'              => $paginated->firstItem() ?? 0,
                'to'                => $paginated->lastItem() ?? 0,
                'total_pemasukan'   => $totalPemasukan,
                'total_pengeluaran' => $totalPengeluaran,
                'saldo_kas'         => $totalPemasukan - $totalPengeluaran,
            ]);
        }

        return view('home.kas.index', compact('title'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipe'       => 'required|in:pemasukan,pengeluaran',
            'nominal'    => 'required|numeric|min:1',
            'keterangan' => 'required|string|max:255',
            'kategori'   => 'nullable|string|max:100',
            'tanggal'    => 'required|date',
        ], [
            'tipe.required'       => 'Tipe wajib dipilih.',
            'nominal.required'    => 'Nominal wajib diisi.',
            'nominal.min'         => 'Nominal minimal Rp 1.',
            'keterangan.required' => 'Keterangan wajib diisi.',
            'tanggal.required'    => 'Tanggal wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            $appId = auth()->user()->app->id ?? null;

            $kas = Kas::create([
                'tipe'       => $request->tipe,
                'nominal'    => $request->nominal,
                'keterangan' => $request->keterangan,
                'kategori'   => $request->kategori,
                'tanggal'    => $request->tanggal,
                'app'        => $appId,
                'user_id'    => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Data kas berhasil ditambahkan.',
                'data'    => $kas,
            ]);
        } catch (\Throwable $e) {
            Log::error('Kas store error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tipe'       => 'required|in:pemasukan,pengeluaran',
            'nominal'    => 'required|numeric|min:1',
            'keterangan' => 'required|string|max:255',
            'kategori'   => 'nullable|string|max:100',
            'tanggal'    => 'required|date',
        ], [
            'tipe.required'       => 'Tipe wajib dipilih.',
            'nominal.required'    => 'Nominal wajib diisi.',
            'nominal.min'         => 'Nominal minimal Rp 1.',
            'keterangan.required' => 'Keterangan wajib diisi.',
            'tanggal.required'    => 'Tanggal wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            $kas = Kas::findOrFail($id);
            $kas->update([
                'tipe'       => $request->tipe,
                'nominal'    => $request->nominal,
                'keterangan' => $request->keterangan,
                'kategori'   => $request->kategori,
                'tanggal'    => $request->tanggal,
            ]);

            return response()->json([
                'message' => 'Data kas berhasil diperbarui.',
                'data'    => $kas,
            ]);
        } catch (\Throwable $e) {
            Log::error('Kas update error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $kas = Kas::findOrFail($id);
            $kas->delete();

            return response()->json(['message' => 'Data kas berhasil dihapus.']);
        } catch (\Throwable $e) {
            Log::error('Kas destroy error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
