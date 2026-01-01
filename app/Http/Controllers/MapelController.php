<?php
namespace App\Http\Controllers;

use App\Models\Mapel;
use Illuminate\Http\Request;

class MapelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Mapel::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })
            ->get();
        $title = "Master Mata Pelajaran";
        return view('master.mapel.index', compact('items', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $action = "Tambah";
        $title  = "Form Mata Pelajaran";
        return view('master.mapel.form', compact('action', 'title'));
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

        $item       = new Mapel;
        $item->name = $request->name;
        $item->app  = auth()->user()->app->id;
        $item->save();

        return redirect()->route('dashboard.master.mapel.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Mapel $mapel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mapel $mapel)
    {
        $action = "Edit";
        $title  = "Form Kelas";
        $items  = $mapel;
        return view('master.mapel.form', compact('action', 'title', 'items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mapel $mapel)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $item       = $mapel;
        $item->name = $request->name;
        $item->save();

        return redirect()->route('dashboard.master.mapel.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mapel $mapel)
    {
        $mapel->delete();
        return redirect()->route('dashboard.master.mapel.index');
    }
}
