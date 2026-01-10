<?php

namespace App\Http\Controllers;

use App\Models\AttendanceConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceConfigController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $items = AttendanceConfig::where('app', $user->app->id ?? $user->id) // Assuming logic typical for this app
            ->orderBy('role')
            ->get();
            
        $title = "Konfigurasi Absensi";
        return view('master.absensi.index', compact('items', 'title'));
    }

    public function create()
    {
        $title = "Tambah Konfigurasi Absensi";
        return view('master.absensi.form', compact('title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'role' => 'required',
            'clock_in_start' => 'required',
            'clock_in_end' => 'required',
            'clock_out_start' => 'required',
            'clock_out_end' => 'required',
            'lat' => 'nullable',
            'lng' => 'nullable',
            'radius' => 'nullable|numeric',
        ]);

        $user = Auth::user();
        // Handle app id, assuming user has app relation or similar logic as other controllers
        $appId = $user->app->id ?? ($user->studentData->app ?? $user->teacherData->app ?? null);
        
        // If admin (role 1) usually has direct app or select app? 
        // Based on MapelDayController:
        // if (auth()->user()->app) { $item->app = auth()->user()->app->id; }

        AttendanceConfig::create([
            'app' => $appId,
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
        return view('master.absensi.form', compact('item', 'title'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'role' => 'required',
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
