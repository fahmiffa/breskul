<?php

namespace App\Http\Controllers;

use App\Models\AcademicYears;
use App\Models\Classes;
use App\Models\Head;
use App\Models\Students;
use App\Models\Prodi;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StudentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $items = Students::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })
            ->get();

        $title = "Master " . (config('app.school_mode') ? 'Murid' : 'Mahasiswa');
        $kelas = Classes::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })
            ->get();

        return view('master.murid.index', compact('items', 'title', 'kelas'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file'  => 'required|file|mimes:xlsx,xls,csv',
            'kelas' => 'required|integer|exists:classes,id',
        ]);

        try {
            // Simpan file ke storage untuk diproses oleh job
            $stored = $request->file('file')->store('imports', 'local');

            $jobId = (string) \Illuminate\Support\Str::uuid();
            \Illuminate\Support\Facades\Cache::put("job-progress-{$jobId}", 0, now()->addMinutes(10));

            $appId = auth()->user()->app->id;

            \App\Jobs\ImportStudentsJob::dispatch($stored, (int) $request->kelas, (int) $appId, $jobId);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Job dispatched', 'jobId' => $jobId]);
            }

            return back()->with('success', 'Import dimulai di background.');
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
            return back()->with('err', $e->getMessage());
        }
    }

    public function create()
    {
        $action = "Tambah";
        $kelas  = Classes::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })
            ->get();

        $akademik = AcademicYears::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })->where('status', 1)
            ->get();

        $prodis = null;
        if (!config('app.school_mode')) {
            $prodis = Prodi::when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })->get();
        }

        $title = "Form " . (config('app.school_mode') ? 'Murid' : 'Mahasiswa');
        return view('master.murid.form', compact('action', 'title', 'kelas', 'akademik', 'prodis'));
    }

    public function storeRfid(Request $request, $id)
    {
        $request->validate([
            'rfid' => 'required|string|max:255',
        ]);

        $murid       = Students::findOrFail($id);
        $murid->uuid = $request->rfid;
        $murid->save();

        return redirect()->back()->with('success', 'RFID berhasil disimpan.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'gender'        => 'nullable|in:1,2',
                'place'         => 'nullable|string',
                'birth'         => 'nullable|date',
                'dad'           => 'nullable|string',
                'dadJob'        => 'nullable|string',
                'mom'           => 'nullable|string',
                'momJob'        => 'nullable|string',
                'hp_parent'     => 'nullable|string',
                'email'         => 'nullable|unique:users,email',
                'image'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'sekolah_kelas' => 'nullable|string',
                'alamat'        => 'required|string',
                'hp_siswa'      => 'required',
                "name"          => "required",
                "nis"           => "required",
                "kelas"         => config('app.school_mode') ? "required" : "nullable",
                "prodi"         => !config('app.school_mode') ? "required" : "nullable",
            ],
            [
                'required' => 'Field Wajib disi',
            ]
        );

        DB::beginTransaction();

        try {
            $path = null;
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('images', 'public');
            }

            $userId = DB::table('users')->insertGetId([
                'name'       => $request->name,
                'username'   => UserName($request->name),
                'password'   => Hash::make('breskul'),
                'role'       => 2,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $siswa            = new Students;
            $siswa->user      = $userId;
            $siswa->name      = $request->name;
            $siswa->nis       = $request->nis;
            $siswa->img       = $path;
            $siswa->app       = auth()->user()->app->id;
            $siswa->alamat    = $request->alamat;
            $siswa->place     = $request->place;
            $siswa->birth     = $request->birth;
            $siswa->hp_siswa  = $request->hp_siswa;
            $siswa->dad       = $request->dad;
            $siswa->dadJob    = $request->dadJob;
            $siswa->mom       = $request->mom;
            $siswa->momJob    = $request->momJob;
            $siswa->hp_parent = $request->hp_parent;
            $siswa->gender    = $request->gender;
            $siswa->save();

            $head              = new Head;
            $head->app         = auth()->user()->app->id;
            $head->student_id  = $siswa->id;
            $head->class_id    = $request->kelas;
            $head->prodi_id    = $request->prodi;
            $head->academic_id = $request->akademik;
            $head->status      = 1;
            $head->save();

            DB::commit();
            return redirect()->route('dashboard.master.murid.index');
        } catch (\Throwable $e) {
            DB::rollback();

            if (isset($path)) {
                Storage::disk('public')->delete($path);
            }
            return back()->withInput()->withErrors('Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Students $murid) {}

    public function edit(Students $murid)
    {
        $action   = "Edit";
        $title    = "Form " . (config('app.school_mode') ? 'Murid' : 'Mahasiswa');
        $items    = $murid;
        $akademik = AcademicYears::latest()->get();
        $kelas    = Classes::latest()->get();

        $prodis = null;
        if (!config('app.school_mode')) {
            $prodis = Prodi::when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })->get();
        }

        return view('master.murid.form', compact('action', 'title', 'items', 'kelas', 'akademik', 'prodis'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Students $murid)
    {
        $validated = $request->validate(
            [
                // Wajib diisi
                'gender'        => 'nullable|in:1,2',
                'place'         => 'nullable|string',
                'birth'         => 'nullable|date',
                'dad'           => 'nullable|string',
                'dadJob'        => 'nullable|string',
                'mom'           => 'nullable|string',
                'momJob'        => 'nullable|string',
                'hp_parent'     => 'nullable|string',
                'email'         => 'nullable|unique:users,email',
                'image'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'sekolah_kelas' => 'nullable|string',
                'alamat'        => 'required|string',
                'hp_siswa'      => 'required',
                "name"          => "required",
                "kelas"         => config('app.school_mode') ? "required" : "nullable",
                "prodi"         => !config('app.school_mode') ? "required" : "nullable",
            ],
            [
                'required' => 'Field Wajib disi',
            ]
        );

        DB::beginTransaction();

        try {
            $path = $murid->img;
            if ($request->hasFile('image')) {
                if ($path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
                }
                $path = $request->file('image')->store('images', 'public');
            }

            $siswa            = $murid;
            $siswa->name      = $request->name;
            $siswa->nis       = $request->nis;
            $siswa->img       = $path;
            $siswa->alamat    = $request->alamat;
            $siswa->place     = $request->place;
            $siswa->birth     = $request->birth;
            $siswa->hp_siswa  = $request->hp_siswa;
            $siswa->dad       = $request->dad;
            $siswa->dadJob    = $request->dadJob;
            $siswa->mom       = $request->mom;
            $siswa->momJob    = $request->momJob;
            $siswa->hp_parent = $request->hp_parent;
            $siswa->gender    = $request->gender;
            $siswa->save();

            // Update Head (active head)
            $head = Head::where('student_id', $murid->id)->where('academic_id', $request->akademik)->first();
            if ($head) {
                $head->class_id = $request->kelas;
                $head->prodi_id = $request->prodi;
                $head->save();
            }

            DB::commit();
            return redirect()->route('dashboard.master.murid.index');
        } catch (\Throwable $e) {
            DB::rollback();

            return back()->withErrors('Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Students $murid)
    {
        try {
            DB::beginTransaction();

            // 1. Delete Presents (Absensi)
            \App\Models\Present::where('student_id', $murid->id)->delete();

            // 2. Delete StudentExtracurriculars
            \App\Models\StudentExtracurricular::where('student_id', $murid->id)->delete();

            // 3. Delete Head (and its Bills)
            foreach ($murid->head as $head) {
                // Delete Bills for this Head
                $head->bill()->delete();
                $head->delete();
            }

            // 4. Delete User account if exists
            if ($murid->users) {
                $murid->users->delete();
            }

            // 5. Delete Student
            $murid->delete();

            DB::commit();
            return redirect()->route('dashboard.master.murid.index')->with('success', 'Data murid beserta relasinya berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
