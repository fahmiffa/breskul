<?php
namespace App\Http\Controllers;

use App\Models\Classes;
use Illuminate\Http\Request;

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
        $kela->delete();
        return redirect()->route('dashboard.master.kelas.index');
    }
}
