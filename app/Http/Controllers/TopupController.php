<?php

namespace App\Http\Controllers;

use App\Models\LogSaldo;
use App\Models\Saldo;
use App\Models\Topup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TopupController extends Controller
{
    public function index(Request $request)
    {
        $title = "Verifikasi Topup";
        
        if ($request->ajax() || $request->wantsJson()) {
            $query = Topup::with(['student.reg.kelas', 'student.reg.prodi']);
            
            // Search Filter
            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->whereHas('student', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('nis', 'like', "%{$search}%");
                });
            }
            
            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            $perPage = (int) $request->get('per_page', 15);
            $paginated = $query->latest('id')->paginate($perPage);
            
            $items = $paginated->getCollection()->map(function ($q) {
                $kelas = '-';
                if ($q->student && $q->student->reg) {
                    $kelas = config('app.school_mode') 
                        ? ($q->student->reg->kelas->name ?? '-') 
                        : ($q->student->reg->prodi->name ?? '-');
                }
                
                return [
                    'id'            => $q->id,
                    'student_name'  => $q->student->name ?? '-',
                    'nis'           => $q->student->nis ?? '-',
                    'kelas'         => $kelas,
                    'nominal'       => $q->nominal,
                    'kode_unik'     => $q->kode_unik,
                    'total_nominal' => $q->total_nominal,
                    'status'        => $q->status,
                    'expired_at'    => $q->expired_at ? $q->expired_at->format('Y-m-d H:i') : '-',
                    'created_at'    => $q->created_at->format('Y-m-d H:i'),
                ];
            });
            
            return response()->json([
                'data'         => $items,
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'from'         => $paginated->firstItem() ?? 0,
                'to'           => $paginated->lastItem() ?? 0,
            ]);
        }

        return view('home.topup.index', compact('title'));
    }

    public function verify(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:topups,id',
        ]);

        try {
            DB::beginTransaction();

            $topup = Topup::with('student')->findOrFail($request->id);

            if ($topup->status !== 'pending') {
                return response()->json([
                    'message' => 'Status topup tidak dapat diverifikasi (sudah ' . $topup->status . ')'
                ], 400);
            }

            // Update status
            $topup->status = 'success';
            $topup->save();

            // Tambahkan ke saldo
            $student = $topup->student;
            if ($student) {
                $appId = $student->app ?? (auth()->user()->app->id ?? null);
                
                $saldo = Saldo::firstOrCreate(
                    ['students_id' => $student->id],
                    [
                        'nominal' => 0,
                        'app_id'  => $appId,
                    ]
                );
                
                // yang ditambahkan adalah total_nominal atau nominal? Biasanya nominal. Tapi uang yang ditransfer adalah total_nominal.
                // Kita tambahkan total_nominal ke saldo.
                $saldo->nominal += $topup->total_nominal;
                $saldo->save();

                LogSaldo::create([
                    'saldo_id'    => $saldo->id,
                    'students_id' => $student->id,
                    'tipe'        => 'kredit',
                    'nominal'     => $topup->total_nominal,
                    'keterangan'  => 'Top up saldo (ID: ' . $topup->id . ') via Verifikasi Manual',
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Topup berhasil diverifikasi dan saldo ditambahkan.'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Topup verify error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }
}
