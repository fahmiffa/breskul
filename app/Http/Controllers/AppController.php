<?php
namespace App\Http\Controllers;

use App\Models\App;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AppController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = App::has('user')->with('user')->latest()->get();
        $title = "Master App";
        return view('master.app.index', compact('items', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $action = "Tambah";
        $title  = "Form App";
        return view('master.app.form', compact('action', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "name"  => "required",
            "hp"    => "required|unique:users,nomor",
            "email" => "required|email|unique:users,email",
        ],
            [
                'required' => 'Field Wajib disi',
                'email'    => 'Email tidak valid',
                'unique'   => ':attribute sudah terdaftar',
            ]);

        DB::beginTransaction();

        try {

            $user           = new User;
            $user->name     = $request->name;
            $user->email    = $request->email;
            $user->role     = 1;
            $user->status   = 1;
            $user->nomor    = $request->hp;
            $user->password = Hash::make('rahasia');
            $user->save();

            $items          = new App;
            $items->name    = $request->name;
            $items->user_id = $user->id;
            $items->save();

            DB::commit();
            return redirect()->route('dashboard.master.app.index');
        } catch (\Throwable $e) {
            DB::rollback();
            return back()->withInput()->withErrors('Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(App $app)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(App $app)
    {
        $action = "Edit";
        $title  = "Form App";
        $items  = $app;
        return view('master.app.form', compact('action', 'title', 'items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, App $app)
    {
        $userId = $app->user->id; // misal kamu dapat ID user dari relasi app

        $validated = $request->validate([
            'name'  => 'required',
            'hp'    => 'required|unique:users,nomor,' . $userId,
            'email' => 'required|email|unique:users,email,' . $userId,
        ], [
            'required' => 'Field wajib diisi',
            'email'    => 'Email tidak valid',
            'unique'   => ':attribute sudah terdaftar',
        ]);

        DB::beginTransaction();

        try {

            $user        = $app->user;
            $user->name  = $request->name;
            $user->email = $request->email;
            $user->nomor = $request->hp;
            // $user->password = Hash::make('rahasia');
            $user->save();

            $items       = $app;
            $items->name = $request->name;
            $items->save();

            DB::commit();
            return redirect()->route('dashboard.master.app.index');
        } catch (\Throwable $e) {
            DB::rollback();
            return back()->withInput()->withErrors('Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(App $app)
    {
        //
    }
}
