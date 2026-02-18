<?php

namespace App\Http\Controllers;

use App\Models\Ujian;
use App\Models\UjianStudent;
use App\Models\Students;
use App\Models\Classes;
use App\Models\Teach;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UjianAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $teach = Teach::where('user_id', Auth::id())->first();
        if (!$teach) return abort(403);

        $query = UjianStudent::whereHas('ujian', function ($q) use ($teach) {
            $q->where('teach_id', $teach->id);
        });

        if ($request->search) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->class_id) {
            $query->whereHas('student.head', function ($q) use ($request) {
                $q->where('class_id', $request->class_id)->where('status', 1);
            });
        }

        $items = $query->with(['ujian', 'student.Kelas'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $classes = Classes::where('app', $teach->app)->get();
        $title = "Daftar Exam";
        return view('master.ujian_assignment.index', compact('items', 'title', 'classes'));
    }

    public function create(Request $request)
    {
        $teach = Teach::where('user_id', Auth::id())->first();
        if (!$teach) return abort(403);

        $ujians = Ujian::where('teach_id', $teach->id)->get();
        $classes = Classes::where('app', $teach->app)->get();

        $selectedClass = $request->class_id;

        // Fetch students in the same app
        $query = Students::where('app', $teach->app);

        if ($selectedClass) {
            $query->whereHas('head', function ($q) use ($selectedClass) {
                $q->where('class_id', $selectedClass)->where('status', 1); // active students in this class
            });
        }

        $students = $query->with('Kelas')->get();

        $title = "Tambah Penugasan Ujian";
        return view('master.ujian_assignment.form', compact('ujians', 'classes', 'students', 'title', 'selectedClass'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ujian_id' => 'required|exists:ujians,id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
        ]);

        foreach ($request->student_ids as $studentId) {
            // Avoid duplicate assignments
            UjianStudent::firstOrCreate([
                'ujian_id' => $request->ujian_id,
                'student_id' => $studentId,
            ]);
        }

        return redirect()->route('dashboard.penjadwalan-ujian.index')->with('success', 'Ujian berhasil ditugaskan ke murid.');
    }

    public function destroy(UjianStudent $penjadwalan_ujian)
    {
        $penjadwalan_ujian->delete();
        return back()->with('success', 'Penugasan berhasil dihapus.');
    }

    public function show($id)
    {
        $item = UjianStudent::with(['ujian.mapel', 'student'])->findOrFail($id);

        // Auto-recalculate score if answers exist and already finished
        if ($item->status == 2 && !empty($item->answers)) {
            $newScore = \App\Models\Soal::calculateScore($item->ujian->soal_id ?? [], $item->answers);
            if ($item->score != $newScore) {
                $item->score = $newScore;
                $item->save();
            }
        }

        $soalIds = $item->ujian->soal_id ?? [];
        $soals = \App\Models\Soal::whereIn('id', $soalIds)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'item' => $item,
                'ujian' => $item->ujian,
                'student' => $item->student,
                'soals' => $soals,
                'answers' => $item->answers ?? []
            ]
        ]);
    }
}
