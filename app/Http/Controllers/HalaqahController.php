<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Halaqah;
use App\Models\HalaqahStudent;
use App\Models\Head;
use App\Models\Students;
use App\Models\Teach;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HalaqahController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Halaqah::with(['teach', 'students.student.Kelas'])->latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->whereHas('teach', function ($q) {
                    $q->where('app', auth()->user()->app->id);
                });
            })
            ->get();
        $title = "Master Halaqah";
        return view('master.halaqah.index', compact('items', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $action = "Tambah";
        $title  = "Form Halaqah";
        $teaches = Teach::when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
            $query->where('app', auth()->user()->app->id);
        })->get();

        $classes = Classes::when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
            $query->where('app', auth()->user()->app->id);
        })
        ->whereHas('students', function ($query) {
            $query->where('boarding', 1);
        })
        ->get();

        $students = Students::where('boarding', 1)
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })->get();

        return view('master.halaqah.form', compact('action', 'title', 'teaches', 'classes', 'students'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama'        => 'required|string|max:255',
            'teach_id'    => 'required|exists:teaches,id',
            'hari'        => 'required|string',
            'waktu'       => 'required|string',
            'status'      => 'required|in:0,1',
            'keterangan'  => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->only('nama', 'teach_id', 'hari', 'waktu', 'status', 'keterangan');
            $halaqah = Halaqah::create($data);

            if ($request->has('students_id')) {
                foreach ($request->students_id as $studentId) {
                    HalaqahStudent::create([
                        'halaqah_id'  => $halaqah->id,
                        'students_id' => $studentId,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('dashboard.master.halaqah.index')->with('success', 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors($e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Halaqah $halaqah)
    {
        $halaqah->load(['teach', 'students.student.Kelas']);
        $title = "Detail Halaqah: " . $halaqah->nama;
        return view('master.halaqah.show', compact('halaqah', 'title'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Halaqah $halaqah)
    {
        $action = "Edit";
        $title  = "Form Halaqah";
        $items  = $halaqah->load('students');
        $teaches = Teach::when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
            $query->where('app', auth()->user()->app->id);
        })->get();

        $classes = Classes::when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
            $query->where('app', auth()->user()->app->id);
        })
        ->whereHas('students', function ($query) {
            $query->where('boarding', 1);
        })
        ->get();

        $students = Students::where('boarding', 1)
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })->get();

        $selectedStudents = $halaqah->students->pluck('students_id')->toArray();

        return view('master.halaqah.form', compact('action', 'title', 'items', 'teaches', 'classes', 'students', 'selectedStudents'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Halaqah $halaqah)
    {
        $request->validate([
            'nama'        => 'required|string|max:255',
            'teach_id'    => 'required|exists:teaches,id',
            'hari'        => 'required|string',
            'waktu'       => 'required|string',
            'status'      => 'required|in:0,1',
            'keterangan'  => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->only('nama', 'teach_id', 'hari', 'waktu', 'status', 'keterangan');
            $halaqah->update($data);

            // Sync students
            HalaqahStudent::where('halaqah_id', $halaqah->id)->delete();
            if ($request->has('students_id')) {
                foreach ($request->students_id as $studentId) {
                    HalaqahStudent::create([
                        'halaqah_id'  => $halaqah->id,
                        'students_id' => $studentId,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('dashboard.master.halaqah.index')->with('success', 'Data berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors($e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Halaqah $halaqah)
    {
        HalaqahStudent::where('halaqah_id', $halaqah->id)->delete();
        $halaqah->delete();
        return redirect()->route('dashboard.master.halaqah.index')->with('success', 'Data berhasil dihapus');
    }

    /**
     * Get students by class (AJAX)
     */
    public function getStudentsByClass(Request $request)
    {
        $classId = $request->class_id;
        $studentIds = Head::where('class_id', $classId)
            ->where('status', 1)
            ->pluck('student_id');

        $students = Students::whereIn('id', $studentIds)
            ->where('boarding', 1)
            ->get(['id', 'name']);

        return response()->json($students);
    }
}
