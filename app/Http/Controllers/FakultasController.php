<?php

namespace App\Http\Controllers;

use App\Models\Fakultas;
use Illuminate\Http\Request;

class FakultasController extends Controller
{
    public function index()
    {
        $items = Fakultas::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })
            ->get();
        $title = "Master Fakultas";
        return view('master.fakultas.index', compact('items', 'title'));
    }

    public function create()
    {
        $action = "Tambah";
        $title  = "Form Fakultas";
        return view('master.fakultas.form', compact('action', 'title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'kode' => 'required|string|max:50',
        ]);

        $item       = new Fakultas;
        $item->name = $request->name;
        $item->kode = $request->kode;
        $item->app  = auth()->user()->app->id ?? null;
        $item->save();

        return redirect()->route('dashboard.master.fakultas.index')->with('success', 'Fakultas berhasil ditambahkan');
    }

    public function edit(Fakultas $fakulta)
    {
        $action = "Edit";
        $title  = "Form Fakultas";
        $items  = $fakulta;
        return view('master.fakultas.form', compact('action', 'title', 'items'));
    }

    public function update(Request $request, Fakultas $fakulta)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'kode' => 'required|string|max:50',
        ]);

        $fakulta->update([
            'name' => $request->name,
            'kode' => $request->kode,
        ]);

        return redirect()->route('dashboard.master.fakultas.index')->with('success', 'Fakultas berhasil diperbarui');
    }

    public function destroy(Fakultas $fakulta)
    {
        $fakulta->delete();
        return redirect()->route('dashboard.master.fakultas.index')->with('success', 'Fakultas berhasil dihapus');
    }
}
