<?php

namespace App\Http\Controllers;

use App\Models\App;
use App\Models\Jabatan;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    public function index()
    {
        $items = Jabatan::with('app')->orderBy('name', 'asc')->get();
        return view('master.jabatan.index', [
            'title' => 'Master Jabatan',
            'items' => $items
        ]);
    }

    public function create()
    {
        $apps = App::orderBy('name', 'asc')->get();
        return view('master.jabatan.form', [
            'action' => 'Tambah Jabatan',
            'apps' => $apps
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'app_id' => 'nullable|exists:apps,id'
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
        $apps = App::orderBy('name', 'asc')->get();
        return view('master.jabatan.form', [
            'action' => 'Edit Jabatan',
            'items' => $jabatan,
            'apps' => $apps
        ]);
    }

    public function update(Request $request, Jabatan $jabatan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'app_id' => 'nullable|exists:apps,id'
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
