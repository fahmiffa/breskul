<?php

namespace App\Http\Controllers;

use App\Models\Teach;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeachController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Teach::latest()
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
                'password'   => Hash::make('guru'),
                'role'       => 3,
                'status'     => 1,
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
                'gender' => 'nullable|in:1,2',
                'alamat' => 'required|string',
                "name"   => "required",
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
            $items->save();

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
        $guru->delete();
        $guru->mapel()->delete();
        $guru->extra()->delete();
        return redirect()->route('dashboard.master.guru.index');
    }
}
