<?php

namespace App\Http\Controllers;

use App\Models\App;
use App\Models\AttendanceConfig;
use App\Models\Jabatan;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceConfigController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = AttendanceConfig::with(['employee.jabatan', 'employee.app', 'appData']);

        $appId = ($user->role == 1 && $user->app) ? $user->app->id : null;

        if ($appId) {
            $query->where('app', $appId);
        }

        // Filter Role Target
        if ($request->filled('role_target')) {
            if ($request->role_target === 'murid') {
                $query->whereNull('employee_id');
            } elseif ($request->role_target === 'karyawan') {
                $query->whereNotNull('employee_id');
            }
        }

        // Filter Jabatan
        if ($request->filled('jabatan_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('jabatan_id', $request->jabatan_id);
            });
        }

        $items = $query->orderBy('name')->paginate(10)->withQueryString();

        if ($appId) {
            $jabatans = Jabatan::where('app_id', $appId)->orWhereNull('app_id')->orderBy('name')->get();
        } else {
            $jabatans = Jabatan::orderBy('name')->get();
        }
            
        $title = "Konfigurasi Absensi";
        return view('master.absensi.index', compact('items', 'title', 'jabatans'));
    }

    public function create()
    {
        $title = "Tambah Konfigurasi Absensi";
        $user = Auth::user();
        $apps = [];
        $employees = [];
        $existingConfigs = [];

        if ($user->role == 1 && $user->app) {
            $appId = $user->app->id;
            $jabatans = Jabatan::where(function ($q) use ($appId) {
                $q->where('app_id', $appId)->orWhereNull('app_id');
            })->orderBy('name')->get();
            $employees = Employee::with('jabatan')
                ->where('app_id', $appId)
                ->whereDoesntHave('attendanceConfig')
                ->orderBy('name')->get();
            $existingConfigs = AttendanceConfig::where('app', $appId)->orderBy('name')->get();
        } else {
            $apps = App::orderBy('name')->get();
            $jabatans = Jabatan::with('app')->orderBy('name')->get();
            $employees = Employee::with('jabatan', 'app')
                ->whereDoesntHave('attendanceConfig')
                ->orderBy('name')->get();
            $existingConfigs = AttendanceConfig::orderBy('name')->get();
        }

        return view('master.absensi.form', compact('title', 'jabatans', 'apps', 'employees', 'existingConfigs'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $isRole0 = $user->role == 0;

        $request->validate([
            'name' => 'required|string|max:255',
            'app' => $isRole0 ? 'required|exists:apps,id' : 'nullable',
            'role_target' => 'required|in:murid,karyawan',
            'employee_ids' => 'required_if:role_target,karyawan|array',
            'clock_in_start' => 'required',
            'clock_in_end' => 'required',
            'clock_out_start' => 'required',
            'clock_out_end' => 'required',
            'lat' => 'nullable',
            'lng' => 'nullable',
            'radius' => 'nullable|numeric',
        ]);

        $appId = $request->app ?? $user->app?->id ?? ($user->studentData->app ?? $user->teacherData->app ?? null);

        $commonData = [
            'app' => $appId,
            'name' => $request->name,
            'clock_in_start' => $request->clock_in_start,
            'clock_in_end' => $request->clock_in_end,
            'clock_out_start' => $request->clock_out_start,
            'clock_out_end' => $request->clock_out_end,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'radius' => $request->radius ?? 100,
        ];

        if ($request->role_target === 'karyawan' && !empty($request->employee_ids)) {
            foreach ($request->employee_ids as $empId) {
                $data = $commonData;
                $data['employee_id'] = $empId;
                
                // Jika isRole0 dan tidak pilih app, coba ambil dari employee
                if (!$appId) {
                    $emp = \App\Models\Employee::find($empId);
                    if ($emp && $emp->app_id) {
                        $data['app'] = $emp->app_id;
                    }
                }

                AttendanceConfig::create($data);
            }
        } else {
            // Untuk Murid (employee_id null)
            $commonData['employee_id'] = null;
            AttendanceConfig::create($commonData);
        }

        return redirect()->route('dashboard.master.absensi.index')->with('success', 'Konfigurasi berhasil ditambahkan');
    }

    public function edit($id)
    {
        $item = AttendanceConfig::findOrFail($id);
        $title = "Edit Konfigurasi Absensi";
        $user = Auth::user();
        $apps = [];
        $employees = [];
        $existingConfigs = [];

        if ($user->role == 1 && $user->app) {
            $appId = $user->app->id;
            $jabatans = Jabatan::where(function ($q) use ($appId) {
                $q->where('app_id', $appId)->orWhereNull('app_id');
            })->orderBy('name')->get();
            $employees = Employee::with('jabatan')->where('app_id', $appId)->orderBy('name')->get();
            $existingConfigs = AttendanceConfig::where('app', $appId)->where('id', '!=', $id)->orderBy('name')->get();
        } else {
            $apps = App::orderBy('name')->get();
            $jabatans = Jabatan::with('app')->orderBy('name')->get();
            $employees = Employee::with('jabatan', 'app')->orderBy('name')->get();
            $existingConfigs = AttendanceConfig::where('id', '!=', $id)->orderBy('name')->get();
        }

        return view('master.absensi.form', compact('item', 'title', 'jabatans', 'apps', 'employees', 'existingConfigs'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $isRole0 = $user->role == 0;

        $request->validate([
            'name' => 'required|string|max:255',
            'app' => $isRole0 ? 'nullable|exists:apps,id' : 'nullable',
            'clock_in_start' => 'required',
            'clock_in_end' => 'required',
            'clock_out_start' => 'required',
            'clock_out_end' => 'required',
            'lat' => 'nullable',
            'lng' => 'nullable',
            'radius' => 'nullable|numeric',
        ]);

        $item = AttendanceConfig::findOrFail($id);

        $data = $request->except(['_token', '_method', 'role_target', 'employee_ids']);
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
