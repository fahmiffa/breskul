<?php

namespace App\Http\Controllers;

use App\Models\Prodi;
use App\Models\Fakultas;
use Illuminate\Http\Request;

class ProdiController extends Controller
{
    public function index()
    {
        $items = Prodi::with('fakultas')->latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })
            ->get();
        $title = "Master Prodi";
        return view('master.prodi.index', compact('items', 'title'));
    }

    public function create()
    {
        $action = "Tambah";
        $title  = "Form Prodi";
        $fakultas = Fakultas::when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
            $query->where('app', auth()->user()->app->id);
        })->get();
        return view('master.prodi.form', compact('action', 'title', 'fakultas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'fakultas_id' => 'required|exists:fakultas,id',
        ]);

        $item              = new Prodi;
        $item->name        = $request->name;
        $item->fakultas_id = $request->fakultas_id;
        $item->app         = auth()->user()->app->id ?? null;
        $item->save();

        return redirect()->route('dashboard.master.prodi.index')->with('success', 'Prodi berhasil ditambahkan');
    }

    public function edit(Prodi $prodi)
    {
        $action = "Edit";
        $title  = "Form Prodi";
        $items  = $prodi;
        $fakultas = Fakultas::when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
            $query->where('app', auth()->user()->app->id);
        })->get();
        return view('master.prodi.form', compact('action', 'title', 'items', 'fakultas'));
    }

    public function update(Request $request, Prodi $prodi)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'fakultas_id' => 'required|exists:fakultas,id',
        ]);

        $prodi->update([
            'name' => $request->name,
            'fakultas_id' => $request->fakultas_id,
        ]);

        return redirect()->route('dashboard.master.prodi.index')->with('success', 'Prodi berhasil diperbarui');
    }

    public function destroy(Prodi $prodi)
    {
        $prodi->delete();
        return redirect()->route('dashboard.master.prodi.index')->with('success', 'Prodi berhasil dihapus');
    }
}
