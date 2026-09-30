<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Jabatan;
use App\Models\Teach;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TeachController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Teach::with('user')
            ->latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })
            ->get();

        $title = "Master " . (config('app.school_mode') ? 'Guru' : 'Dosen');
        return view('master.guru.index', compact('items', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $action = "Tambah";
        $title  = "Form " . (config('app.school_mode') ? 'Guru' : 'Dosen');
        return view('master.guru.form', compact('action', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'gender' => 'nullable|in:1,2',
                'alamat' => 'required|string',
                "name"   => "required",
                'nomor'  => 'nullable|string|max:20',
                'email'  => 'nullable|email|max:255|unique:users,email',
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

            $guruJabatan = Jabatan::where('name', 'like', '%guru%')->orWhere('name', 'like', '%dosen%')->first();
            $jabatanId   = $guruJabatan ? $guruJabatan->id : null;

            $userId = DB::table('users')->insertGetId([
                'name'       => $request->name,
                'username'   => UserName($request->name),
                'password'   => Hash::make('binainsantaqwa'),
                'role'       => 3,
                'status'     => 1,
                'nomor'      => $request->filled('nomor') ? $request->nomor : null,
                'email'      => $request->filled('email') ? $request->email : null,
                'jabatan_id' => $jabatanId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $items          = new Teach;
            $items->user_id = $userId;
            $items->name    = $request->name;
            $items->alamat  = $request->alamat;
            $items->gender  = $request->gender;
            $items->app     = auth()->user()->app->id;
            $items->save();

            Employee::create([
                'app_id'        => auth()->user()->app->id ?? null,
                'name'          => $request->name,
                'alamat'        => $request->alamat,
                'jenis_kelamin' => $request->gender,
                'user_id'       => $userId,
                'jabatan_id'    => $jabatanId,
            ]);

            DB::commit();
            return redirect()->route('dashboard.master.guru.index');
        } catch (\Throwable $e) {
            DB::rollback();
            return back()->withInput()->withErrors('Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Teach $teach)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Teach $guru)
    {
        $guru->load('user');
        $action = "Edit";
        $title  = "Form " . (config('app.school_mode') ? 'Guru' : 'Dosen');
        $items  = $guru;
        return view('master.guru.form', compact('action', 'title', 'items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Teach $guru)
    {
        $validated = $request->validate(
            [
                'gender'  => 'nullable|in:1,2',
                'alamat'  => 'required|string',
                "name"    => "required",
                "jenjang" => "nullable|in:tk,sd,smp,sma",
                'nomor'   => 'nullable|string|max:20',
                'email'   => [
                    'nullable',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($guru->user_id),
                ],
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

            $items         = $guru;
            $items->name   = $request->name;
            $items->alamat = $request->alamat;
            $items->gender = $request->gender;
            $items->jenjang = $request->jenjang;
            $items->save();

            Employee::where('user_id', $guru->user_id)->update([
                'name'          => $request->name,
                'alamat'        => $request->alamat,
                'jenis_kelamin' => $request->gender,
            ]);

            if ($guru->user_id) {
                DB::table('users')->where('id', $guru->user_id)->update([
                    'name'       => $request->name,
                    'nomor'      => $request->filled('nomor') ? $request->nomor : null,
                    'email'      => $request->filled('email') ? $request->email : null,
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('dashboard.master.guru.index');
        } catch (\Throwable $e) {
            DB::rollback();
            return back()->withInput()->withErrors('Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Teach $guru)
    {
        Employee::where('user_id', $guru->user_id)->delete();
        $guru->delete();
        $guru->mapel()->delete();
        $guru->extra()->delete();
        return redirect()->route('dashboard.master.guru.index');
    }
}
