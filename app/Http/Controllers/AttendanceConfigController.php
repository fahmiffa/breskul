<?php

namespace App\Http\Controllers;

use App\Models\AttendanceConfig;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceConfigController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $items = AttendanceConfig::with('jabatan')
            ->where('app', $user->app->id ?? $user->id)
            ->orderBy('role')
            ->get();
            
        $title = "Konfigurasi Absensi";
        return view('master.absensi.index', compact('items', 'title'));
    }

    public function create()
    {
        $title = "Tambah Konfigurasi Absensi";
        $jabatans = Jabatan::orderBy('name')->get();
        return view('master.absensi.form', compact('title', 'jabatans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'jabatan_id' => 'nullable|exists:jabatans,id',
            'role' => 'nullable',
            'clock_in_start' => 'required',
            'clock_in_end' => 'required',
            'clock_out_start' => 'required',
            'clock_out_end' => 'required',
            'lat' => 'nullable',
            'lng' => 'nullable',
            'radius' => 'nullable|numeric',
        ]);

        $user = Auth::user();
        $appId = $user->app->id ?? ($user->studentData->app ?? $user->teacherData->app ?? null);

        AttendanceConfig::create([
            'app' => $appId,
            'name' => $request->name,
            'jabatan_id' => $request->jabatan_id,
            'role' => $request->role,
            'clock_in_start' => $request->clock_in_start,
            'clock_in_end' => $request->clock_in_end,
            'clock_out_start' => $request->clock_out_start,
            'clock_out_end' => $request->clock_out_end,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'radius' => $request->radius ?? 100,
        ]);

        return redirect()->route('dashboard.master.absensi.index')->with('success', 'Konfigurasi berhasil ditambahkan');
    }

    public function edit($id)
    {
        $item = AttendanceConfig::findOrFail($id);
        $title = "Edit Konfigurasi Absensi";
        $jabatans = Jabatan::orderBy('name')->get();
        return view('master.absensi.form', compact('item', 'title', 'jabatans'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'jabatan_id' => 'nullable|exists:jabatans,id',
            'role' => 'nullable',
            'clock_in_start' => 'required',
            'clock_in_end' => 'required',
            'clock_out_start' => 'required',
            'clock_out_end' => 'required',
            'lat' => 'nullable',
            'lng' => 'nullable',
            'radius' => 'nullable|numeric',
        ]);

        $item = AttendanceConfig::findOrFail($id);
        $item->update($request->all());

        return redirect()->route('dashboard.master.absensi.index')->with('success', 'Konfigurasi berhasil diperbarui');
    }

    public function destroy($id)
    {
        $item = AttendanceConfig::findOrFail($id);
        $item->delete();
        return redirect()->route('dashboard.master.absensi.index')->with('success', 'Konfigurasi berhasil dihapus');
    }
}
