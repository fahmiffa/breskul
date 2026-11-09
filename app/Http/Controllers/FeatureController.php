<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class FeatureController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     * Hanya bisa diakses oleh user dengan role 0.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('isRole'),
            new Middleware('role:0'),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Feature::latest()->get();

        return view('master.fitur.index', [
            'title' => 'Master Fitur',
            'items' => $items,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('master.fitur.form', [
            'action' => 'Tambah Fitur',
            'title'  => 'Form Tambah Fitur',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'Nama Fitur wajib diisi',
        ]);

        Feature::create([
            'name' => $request->name,
        ]);

        return redirect()->route('dashboard.master.fitur.index')->with('success', 'Berhasil menambahkan data fitur.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return redirect()->route('dashboard.master.fitur.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $feature = $id instanceof Feature ? $id : Feature::findOrFail($id);

        return view('master.fitur.form', [
            'action' => 'Edit Fitur',
            'title'  => 'Form Edit Fitur',
            'items'  => $feature,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'Nama Fitur wajib diisi',
        ]);

        $feature = $id instanceof Feature ? $id : Feature::findOrFail($id);

        $feature->update([
            'name' => $request->name,
        ]);

        return redirect()->route('dashboard.master.fitur.index')->with('success', 'Berhasil memperbarui data fitur.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $feature = $id instanceof Feature ? $id : Feature::findOrFail($id);
        $feature->delete();

        return redirect()->route('dashboard.master.fitur.index')->with('success', 'Berhasil menghapus data fitur.');
    }
}
