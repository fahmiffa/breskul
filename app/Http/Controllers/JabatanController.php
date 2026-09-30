<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    public function index()
    {
        $items = Jabatan::orderBy('name', 'asc')->get();
        return view('master.jabatan.index', [
            'title' => 'Master Jabatan',
            'items' => $items
        ]);
    }

    public function create()
    {
        return view('master.jabatan.form', [
            'action' => 'Tambah Jabatan'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        Jabatan::create($request->all());

        return redirect()->route('dashboard.master.jabatan.index')->with('success', 'Berhasil menambahkan jabatan.');
    }

    public function show(Jabatan $jabatan)
    {
        //
    }

    public function edit(Jabatan $jabatan)
    {
        return view('master.jabatan.form', [
            'action' => 'Edit Jabatan',
            'items' => $jabatan
        ]);
    }

    public function update(Request $request, Jabatan $jabatan)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $jabatan->update($request->all());

        return redirect()->route('dashboard.master.jabatan.index')->with('success', 'Berhasil mengubah jabatan.');
    }

    public function destroy(Jabatan $jabatan)
    {
        $jabatan->delete();

        return redirect()->route('dashboard.master.jabatan.index')->with('success', 'Berhasil menghapus jabatan.');
    }
}
