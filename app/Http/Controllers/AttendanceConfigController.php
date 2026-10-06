<?php

namespace App\Http\Controllers;

use App\Models\App;
use App\Models\AttendanceConfig;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceConfigController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = AttendanceConfig::with(['jabatan.app', 'appData']);

        if ($user->role == 1 && $user->app) {
            $query->where('app', $user->app->id);
        }

        $items = $query->orderBy('role')->get();
            
        $title = "Konfigurasi Absensi";
        return view('master.absensi.index', compact('items', 'title'));
    }

    public function create()
    {
        $title = "Tambah Konfigurasi Absensi";
        $user = Auth::user();
        $apps = [];

        if ($user->role == 1 && $user->app) {
            $appId = $user->app->id;
            $jabatans = Jabatan::where(function ($q) use ($appId) {
                $q->where('app_id', $appId)->orWhereNull('app_id');
            })->orderBy('name')->get();
        } else {
            $apps = App::orderBy('name')->get();
            $jabatans = Jabatan::with('app')->orderBy('name')->get();
        }

        return view('master.absensi.form', compact('title', 'jabatans', 'apps'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $isRole0 = $user->role == 0;

        $request->validate([
            'name' => 'required|string|max:255',
            'app' => $isRole0 ? 'required|exists:apps,id' : 'nullable',
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

        $appId = $request->app ?? $user->app?->id ?? ($user->studentData->app ?? $user->teacherData->app ?? null);

        if (!$appId && $request->jabatan_id) {
            $jabatan = Jabatan::find($request->jabatan_id);
            if ($jabatan && $jabatan->app_id) {
                $appId = $jabatan->app_id;
            }
        }

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
        $user = Auth::user();
        $apps = [];

        if ($user->role == 1 && $user->app) {
            $appId = $user->app->id;
            $jabatans = Jabatan::where(function ($q) use ($appId) {
                $q->where('app_id', $appId)->orWhereNull('app_id');
            })->orderBy('name')->get();
        } else {
            $apps = App::orderBy('name')->get();
            $jabatans = Jabatan::with('app')->orderBy('name')->get();
        }

        return view('master.absensi.form', compact('item', 'title', 'jabatans', 'apps'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $isRole0 = $user->role == 0;

        $request->validate([
            'name' => 'required|string|max:255',
            'app' => $isRole0 ? 'nullable|exists:apps,id' : 'nullable',
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

        $data = $request->except(['_token', '_method']);
        if ($isRole0 && $request->filled('app')) {
            $data['app'] = $request->app;
        }

        $item->update($data);

        return redirect()->route('dashboard.master.absensi.index')->with('success', 'Konfigurasi berhasil diperbarui');
    }

    public function destroy($id)
    {
        $item = AttendanceConfig::findOrFail($id);
        $item->delete();
        return redirect()->route('dashboard.master.absensi.index')->with('success', 'Konfigurasi berhasil dihapus');
    }
}
