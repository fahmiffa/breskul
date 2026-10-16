<?php

namespace App\Http\Controllers;

use App\Models\App;
use App\Models\Bank;
use Illuminate\Http\Request;

class BankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $appId = auth()->user()->app->id ?? null;
        $isAppUser = auth()->user()->role == 1 && $appId;

        $items = Bank::with('app')
            ->when($isAppUser, function ($q) use ($appId) {
                $q->where('app_id', $appId);
            })
            ->latest()
            ->get();

        return view('master.bank.index', [
            'title' => 'Master Bank',
            'items' => $items,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $apps = App::orderBy('name', 'asc')->get();

        return view('master.bank.form', [
            'action' => 'Tambah Bank',
            'title'  => 'Form Tambah Bank',
            'apps'   => $apps,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'number' => 'required|string|max:100',
            'app_id' => 'nullable|exists:apps,id',
        ], [
            'name.required'   => 'Nama Bank wajib diisi',
            'number.required' => 'Nomor Rekening wajib diisi',
        ]);

        $appId = auth()->user()->app->id ?? $request->app_id;

        Bank::create([
            'name'   => $request->name,
            'number' => $request->number,
            'app_id' => $appId,
        ]);

        return redirect()->route('dashboard.master.bank.index')->with('success', 'Berhasil menambahkan data bank.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Bank $bank)
    {
        return redirect()->route('dashboard.master.bank.edit', $bank->id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bank $bank)
    {
        $apps = App::orderBy('name', 'asc')->get();

        return view('master.bank.form', [
            'action' => 'Edit Bank',
            'title'  => 'Form Edit Bank',
            'items'  => $bank,
            'apps'   => $apps,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bank $bank)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'number' => 'required|string|max:100',
            'app_id' => 'nullable|exists:apps,id',
        ], [
            'name.required'   => 'Nama Bank wajib diisi',
            'number.required' => 'Nomor Rekening wajib diisi',
        ]);

        $appId = auth()->user()->app->id ?? $request->app_id ?? $bank->app_id;

        $bank->update([
            'name'   => $request->name,
            'number' => $request->number,
            'app_id' => $appId,
        ]);

        return redirect()->route('dashboard.master.bank.index')->with('success', 'Berhasil memperbarui data bank.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bank $bank)
    {
        $bank->delete();

        return redirect()->route('dashboard.master.bank.index')->with('success', 'Berhasil menghapus data bank.');
    }
}
