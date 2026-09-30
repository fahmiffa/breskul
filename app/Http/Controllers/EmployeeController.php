<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Jabatan;
use App\Models\Teach;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Employee::with(['jabatan', 'user'])
            ->latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app_id', auth()->user()->app->id);
            })
            ->get();

        $title = 'Master Karyawan';
        return view('master.karyawan.index', compact('items', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $action   = 'Tambah';
        $title    = 'Form Karyawan';
        $jabatans = Jabatan::orderBy('name', 'asc')->get();
        return view('master.karyawan.form', compact('action', 'title', 'jabatans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'name'          => 'required|string|max:255',
                'alamat'        => 'required|string',
                'jenis_kelamin' => 'required|in:1,2',
                'jabatan_id'    => 'nullable|exists:jabatans,id',
                'nomor'         => 'nullable|string|max:20',
                'email'         => 'nullable|email|max:255|unique:users,email',
            ],
            [
                'required' => 'Field wajib diisi',
            ]
        );

        DB::beginTransaction();

        try {
            $userId = DB::table('users')->insertGetId([
                'name'       => $request->name,
                'username'   => UserName($request->name),
                'password'   => Hash::make('binainsantaqwa'),
                'role'       => 3,
                'status'     => 1,
                'nomor'      => $request->filled('nomor') ? $request->nomor : null,
                'email'      => $request->filled('email') ? $request->email : null,
                'jabatan_id' => $request->jabatan_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $employee                = new Employee;
            $employee->app_id        = auth()->user()->app->id ?? null;
            $employee->name          = $request->name;
            $employee->alamat        = $request->alamat;
            $employee->jenis_kelamin = $request->jenis_kelamin;
            $employee->user_id       = $userId;
            $employee->jabatan_id    = $request->jabatan_id;
            $employee->save();

            $jabatan = $request->jabatan_id ? Jabatan::find($request->jabatan_id) : null;
            $isGuru  = $jabatan && (stripos($jabatan->name, 'guru') !== false || stripos($jabatan->name, 'dosen') !== false);

            if ($isGuru) {
                $teach          = new Teach;
                $teach->user_id = $userId;
                $teach->name    = $request->name;
                $teach->alamat  = $request->alamat;
                $teach->gender  = $request->jenis_kelamin;
                $teach->app     = auth()->user()->app->id ?? null;
                $teach->save();
            }

            DB::commit();
            return redirect()->route('dashboard.master.karyawan.index')->with('success', 'Berhasil menambahkan karyawan.');
        } catch (\Throwable $e) {
            DB::rollback();
            return back()->withInput()->withErrors('Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $karyawan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $karyawan)
    {
        $karyawan->load('user');
        $action   = 'Edit';
        $title    = 'Form Karyawan';
        $items    = $karyawan;
        $jabatans = Jabatan::orderBy('name', 'asc')->get();
        return view('master.karyawan.form', compact('action', 'title', 'items', 'jabatans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $karyawan)
    {
        $request->validate(
            [
                'name'          => 'required|string|max:255',
                'alamat'        => 'required|string',
                'jenis_kelamin' => 'required|in:1,2',
                'jabatan_id'    => 'nullable|exists:jabatans,id',
                'nomor'         => 'nullable|string|max:20',
                'email'         => [
                    'nullable',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($karyawan->user_id),
                ],
            ],
            [
                'required' => 'Field wajib diisi',
            ]
        );

        DB::beginTransaction();

        try {
            $karyawan->name          = $request->name;
            $karyawan->alamat        = $request->alamat;
            $karyawan->jenis_kelamin = $request->jenis_kelamin;
            $karyawan->jabatan_id    = $request->jabatan_id;
            $karyawan->save();

            $jabatan = $request->jabatan_id ? Jabatan::find($request->jabatan_id) : null;
            $isGuru  = $jabatan && (stripos($jabatan->name, 'guru') !== false || stripos($jabatan->name, 'dosen') !== false);

            if ($isGuru) {
                $teach = Teach::withTrashed()->where('user_id', $karyawan->user_id)->first();
                if ($teach) {
                    if ($teach->trashed()) {
                        $teach->restore();
                    }
                    $teach->name   = $request->name;
                    $teach->alamat = $request->alamat;
                    $teach->gender = $request->jenis_kelamin;
                    $teach->save();
                } else {
                    $teach          = new Teach;
                    $teach->user_id = $karyawan->user_id;
                    $teach->name    = $request->name;
                    $teach->alamat  = $request->alamat;
                    $teach->gender  = $request->jenis_kelamin;
                    $teach->app     = auth()->user()->app->id ?? null;
                    $teach->save();
                }
            } else {
                Teach::where('user_id', $karyawan->user_id)->delete();
            }

            if ($karyawan->user_id) {
                DB::table('users')->where('id', $karyawan->user_id)->update([
                    'name'       => $request->name,
                    'nomor'      => $request->filled('nomor') ? $request->nomor : null,
                    'email'      => $request->filled('email') ? $request->email : null,
                    'jabatan_id' => $request->jabatan_id,
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('dashboard.master.karyawan.index')->with('success', 'Berhasil mengubah karyawan.');
        } catch (\Throwable $e) {
            DB::rollback();
            return back()->withInput()->withErrors('Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $karyawan)
    {
        Teach::where('user_id', $karyawan->user_id)->delete();
        $karyawan->delete();
        return redirect()->route('dashboard.master.karyawan.index')->with('success', 'Berhasil menghapus karyawan.');
    }
}
