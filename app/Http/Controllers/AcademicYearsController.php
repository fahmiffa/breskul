<?php
namespace App\Http\Controllers;

use App\Jobs\BulkInsertJob;
use App\Models\AcademicYears;
use App\Models\Head;
use App\Models\Students;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AcademicYearsController extends Controller
{

    public function import(Request $request)
    {
        $akademik = AcademicYears::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })->where('status', 1)
            ->first();

        $head = Students::where('app',auth()->user()->app->id)->latest()->has('reg')->get();

        $data = [];

        foreach ($head as $val) {
            $data[] = [
                "parent"      => $val->reg->id,
                'student_id'  => $val->id,
                'app'         => auth()->user()->app->id,
                'academic_id' => $akademik->id,
                'status'      => 1,
            ];
        }

        if (count($data) > 0) {
            $jobId = (string) Str::uuid();
            $update = true;
            Cache::put("job-progress-{$jobId}", 0, now()->addMinutes(10));
            BulkInsertJob::dispatch($data, $jobId, $update);
            return response()->json([
                'message' => 'Job dispatched',
                'jobId'   => $jobId,
            ]);
        }

        return response()->json([
            'message' => 'No students to process',
        ], 400);
    }

    public function akademik()
    {
        $head = Head::whereHas('akademik', function ($q) {
            $q->where('status', 1);
        })->with('kelas', 'murid', 'akademik')->latest()->get();

        $items = $head->map(function ($item) {
            return [
                'id'       => $item->murid->id,
                'nis'      => $item->murid->nis,
                'name'     => $item->murid->name,
                'kelas'    => $item->kelas ? $item->kelas->name : null,
                'akademik' => $item->akademik->name,
                'jenis'    => $item->murid->jenis,
            ];
        });

        $items = Students::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })->with('kelas', 'academics')
            ->get();

        $title    = "Master Akademik";
        $akademik = AcademicYears::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })->where('status', 1)
            ->doesntHave('head')
            ->first();
        return view('master.akademik.home', compact('items', 'title', 'akademik'));
    }

    public function assignClass(Request $request)
    {
        $validated = $request->validate([
            'student_ids'   => 'required|array',
            'student_ids.*' => 'integer|exists:students,id',
            'class_id'      => 'required|integer|exists:classes,id',
        ], [
            'required' => 'Field wajib diisi',
            'array'    => 'Data harus berupa array',
            'integer'  => 'Data harus berupa angka',
            'exists'   => 'Data tidak ditemukan',
        ]);

        DB::beginTransaction();
        try {
            $akademik = AcademicYears::where('status', 1)->first();

            if (! $akademik) {
                return response()->json(['message' => 'Tidak ada semester aktif'], 400);
            }

            $numb = 0;
            foreach ($validated['student_ids'] as $studentId) {
                $head = Head::where('student_id', $studentId)
                    ->where('academic_id', $akademik->id)
                    ->first();

                if ($head) {
                    $head->class_id = $validated['class_id'];
                    $head->save();
                    $numb += 1;
                }
            }

            if ($numb > 0) {
                DB::commit();
                return response()->json(['message' => 'Kelas berhasil diterapkan untuk ' . $numb . ' murid']);
            } else {
                return response()->json(['message' => 'Kelas tidak valid'], 400);
            }

        } catch (\Throwable $e) {
            DB::rollback();
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $items = AcademicYears::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })
            ->get();
        $title = "Master Semester";
        return view('master.akademik.index', compact('items', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $action = "Tambah Semester";
        $title  = "Form Semester";
        return view('master.akademik.form', compact('action', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "name" => "required",
        ],
            [
                'required' => 'Field Wajib disi',
            ]
        );

        DB::beginTransaction();
        try {
            AcademicYears::where('status', 1)->update(['status' => 0]);

            $items       = new AcademicYears;
            $items->name = $request->name;
            $items->app  = auth()->user()->app->id;
            $items->save();

            DB::commit();
            return redirect()->route('dashboard.master.semester.index');
        } catch (\Throwable $e) {
            DB::rollback();
            return back()->withInput()->withErrors('Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(AcademicYears $academicYears)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AcademicYears $semester)
    {
        $action = "Edit";
        $title  = "Form Semester";
        $items  = $semester;
        return view('master.akademik.form', compact('action', 'title', 'items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AcademicYears $semester)
    {
        $validated = $request->validate([
            'tahun' => 'required|digits:4',
            "name"  => "required",
        ],
            [
                'required' => 'Field Wajib disi',
            ]
        );

        DB::beginTransaction();

        try {
            $items       = $semester;
            $items->name = $request->name;
            $items->save();

            DB::commit();
            return redirect()->route('dashboard.master.semester.index');
        } catch (\Throwable $e) {
            DB::rollback();
            return back()->withInput()->withErrors('Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AcademicYears $semester)
    {
        $semester->delete();
        return redirect()->route('dashboard.master.semester.index');
    }
}
