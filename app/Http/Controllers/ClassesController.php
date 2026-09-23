<?php
namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Head;
use App\Models\MapelDay;
use App\Models\Present;
use App\Models\StudentExtracurricular;
use App\Models\Students;
use App\Models\UjianStudent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClassesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Classes::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })
            ->get();
        $title = "Master Kelas";
        return view('master.kelas.index', compact('items', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $action = "Tambah";
        $title  = "Form Kelas";
        return view('master.kelas.form', compact('action', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ], [
            'required' => 'Field Wajib disi',
        ]);

        $item       = new Classes;
        $item->name = $request->name;
        $item->app  = auth()->user()->app->id;
        $item->save();

        return redirect()->route('dashboard.master.kelas.index');

    }

    /**
     * Display the specified resource.
     */
    public function show(Classes $classes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Classes $kela)
    {
        $action = "Edit";
        $title  = "Form Kelas";
        $items  = $kela;
        return view('master.kelas.form', compact('action', 'title', 'items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Classes $kela)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $item       = $kela;
        $item->name = $request->name;
        $item->save();

        return redirect()->route('dashboard.master.kelas.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Classes $kela)
    {
        DB::beginTransaction();

        try {
            // 1. Ambil data head yang berelasi dengan class_id kelas ini
            $heads = Head::where('class_id', $kela->id)->get();
            $studentIds = $heads->pluck('student_id')->filter()->unique();

            // 2. Ambil data student berdasarkan student_id
            $students = Students::withTrashed()->whereIn('id', $studentIds)->get();
            $userIds = $students->pluck('user')->filter()->unique();

            // Hapus data relasi tiap murid
            foreach ($students as $student) {
                // Hapus absensi / presensi
                Present::where('student_id', $student->id)->delete();

                // Hapus ekstrakurikuler murid
                StudentExtracurricular::where('student_id', $student->id)->delete();

                // Hapus data ujian murid
                UjianStudent::where('student_id', $student->id)->delete();

                // Hapus semua head murid ini beserta bill
                foreach ($student->head as $studentHead) {
                    $studentHead->bill()->delete();
                    $studentHead->delete();
                }

                // Hapus akun user yang berelasi dengan murid
                if ($student->users) {
                    $student->users->delete();
                } elseif ($student->user) {
                    User::where('id', $student->user)->delete();
                }

                // Hapus foto murid di storage jika ada
                if ($student->img) {
                    Storage::disk('public')->delete($student->img);
                }

                // Hapus murid
                $student->delete();
            }

            // Hapus akun user yang mungkin belum terhapus
            if ($userIds->isNotEmpty()) {
                User::whereIn('id', $userIds)->delete();
            }

            // 3. Hapus sisa head dan bill yang berelasi langsung dengan class_id
            foreach ($heads as $head) {
                $head->bill()->delete();
                $head->delete();
            }

            // 4. Hapus jadwal terkait kelas jika ada
            $jadwals = MapelDay::where('class_id', $kela->id)->get();
            foreach ($jadwals as $jadwal) {
                $jadwal->time()->delete();
                $jadwal->delete();
            }

            // 5. Hapus kelas
            $kela->delete();

            DB::commit();

            return redirect()->route('dashboard.master.kelas.index')->with('success', 'Data kelas beserta murid dan akun user berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('dashboard.master.kelas.index')->with('err', 'Gagal menghapus data kelas: ' . $e->getMessage());
        }
    }
}
