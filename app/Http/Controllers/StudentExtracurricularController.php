<?php
namespace App\Http\Controllers;

use App\Models\Extracurricular;
use App\Models\StudentExtracurricular;
use App\Models\Students;
use Illuminate\Http\Request;

class StudentExtracurricularController extends Controller
{
    public function index()
    {
        $items = StudentExtracurricular::with(['student', 'extracurricular'])->latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })
            ->get();
        $title = "Ekstrakurikuler Murid";
        return view('home.ekstrakurikuler.index', compact('items', 'title'));
    }

    public function create()
    {
        $action = "Tambah";
        $title  = "Form Pendaftaran Ekstrakurikuler";
        
        $students = Students::when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
            $query->where('app', auth()->user()->app->id);
        })->get();

        $extras = Extracurricular::when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
            $query->where('app', auth()->user()->app->id);
        })->get();

        return view('home.ekstrakurikuler.form', compact('action', 'title', 'students', 'extras'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'extracurricular_ids' => 'required|array',
            'extracurricular_ids.*' => 'exists:extracurriculars,id',
        ]);

        foreach ($request->student_ids as $student_id) {
            foreach ($request->extracurricular_ids as $extra_id) {
                // Check if already registered
                $exists = StudentExtracurricular::where('student_id', $student_id)
                    ->where('extracurricular_id', $extra_id)
                    ->exists();

                if (!$exists) {
                    $item = new StudentExtracurricular();
                    $item->student_id = $student_id;
                    $item->extracurricular_id = $extra_id;
                    if (auth()->user()->role == 1 && auth()->user()->app) {
                        $item->app = auth()->user()->app->id;
                    }
                    $item->save();
                }
            }
        }

        return redirect()->route('dashboard.ekstrakurikuler.index')->with('success', 'Pendaftaran berhasil');
    }

    public function destroy(StudentExtracurricular $ekstrakurikuler)
    {
        $ekstrakurikuler->delete();
        return redirect()->route('dashboard.ekstrakurikuler.index')->with('success', 'Data berhasil dihapus');
    }
}
