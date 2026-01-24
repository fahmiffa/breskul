<?php

namespace App\Http\Controllers;

use App\Models\Extracurricular;
use App\Models\Teach;
use Illuminate\Http\Request;
use App\Models\StudentExtracurricular;

class ExtracurricularController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Extracurricular::with('guru')->latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })
            ->get();
        $title = "Master " . (config('app.school_mode') ? 'Ekstrakurikuler' : 'UKM');
        return view('master.ekstrakurikuler.index', compact('items', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $action = "Tambah";
        $title  = "Form " . (config('app.school_mode') ? 'Ekstrakurikuler' : 'UKM');
        $teaches = Teach::when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
            $query->where('app', auth()->user()->app->id);
        })->get();
        return view('master.ekstrakurikuler.form', compact('action', 'title', 'teaches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'guru_id' => 'required|exists:teaches,id',
            'waktu' => 'required|date',
        ]);

        $item = new Extracurricular($request->all());
        if (auth()->user()->role == 1 && auth()->user()->app) {
            $item->app = auth()->user()->app->id;
        }
        $item->save();

        return redirect()->route('dashboard.master.ekstrakurikuler.index')->with('success', 'Data berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Extracurricular $ekstrakurikuler)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Extracurricular $ekstrakurikuler)
    {
        $action = "Edit";
        $title  = "Form " . (config('app.school_mode') ? 'Ekstrakurikuler' : 'UKM');
        $items  = $ekstrakurikuler;
        $teaches = Teach::when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
            $query->where('app', auth()->user()->app->id);
        })->get();
        return view('master.ekstrakurikuler.form', compact('action', 'title', 'items', 'teaches'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Extracurricular $ekstrakurikuler)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'guru_id' => 'required|exists:teaches,id',
            'waktu' => 'required|date',
        ]);

        $ekstrakurikuler->update($request->all());

        return redirect()->route('dashboard.master.ekstrakurikuler.index')->with('success', 'Data berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Extracurricular $ekstrakurikuler)
    {
        StudentExtracurricular::where('extracurricular_id', $ekstrakurikuler->id)->delete();
        $ekstrakurikuler->delete();
        return redirect()->route('dashboard.master.ekstrakurikuler.index')->with('success', 'Data berhasil dihapus');
    }
}
