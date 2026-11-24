<?php

namespace App\Http\Controllers;

use App\Models\App;
use App\Models\AttendanceConfig;
use App\Models\AttendanceConfigTime;
use App\Models\Jabatan;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceConfigController extends Controller
{
    private $dayList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = AttendanceConfig::with(['employee.jabatan', 'employee.app', 'appData', 'times']);

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

        // Filter Nama
        if ($request->filled('name')) {
            $name = $request->name;
            $query->where(function ($q) use ($name) {
                $q->where('name', 'like', "%{$name}%")
                  ->orWhereHas('employee', function ($q) use ($name) {
                      $q->where('name', 'like', "%{$name}%");
                  });
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
        $dayList = $this->dayList;

        if ($user->role == 1 && $user->app) {
            $appId = $user->app->id;
            $jabatans = Jabatan::where(function ($q) use ($appId) {
                $q->where('app_id', $appId)->orWhereNull('app_id');
            })->orderBy('name')->get();
            $employees = Employee::with('jabatan')
                ->where('app_id', $appId)
                ->whereDoesntHave('attendanceConfig')
                ->orderBy('name')->get();
            $existingConfigs = AttendanceConfig::with('times')->where('app', $appId)->orderBy('name')->get();
        } else {
            $apps = App::orderBy('name')->get();
            $jabatans = Jabatan::with('app')->orderBy('name')->get();
            $employees = Employee::with('jabatan', 'app')
                ->whereDoesntHave('attendanceConfig')
                ->orderBy('name')->get();
            $existingConfigs = AttendanceConfig::with('times')->orderBy('name')->get();
        }

        return view('master.absensi.form', compact('title', 'jabatans', 'apps', 'employees', 'existingConfigs', 'dayList'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $isRole0 = $user->role == 0;

        $request->validate([
            'name' => 'required|string|max:255',
            'app' => $isRole0 ? 'required|exists:apps,id' : 'nullable',
            'role_target' => 'required|in:murid,karyawan',
            'employee_ids' => 'required_if:role_target,karyawan|array|min:1',
            'days' => 'required|array|min:1',
            'days.*' => 'required|in:' . implode(',', $this->dayList),
            'times' => 'required|array',
            'times.*.clock_in_start' => 'required',
            'times.*.clock_in_end' => 'required',
            'times.*.clock_out_start' => 'required',
            'times.*.clock_out_end' => 'required',
            'times.*.lat' => 'nullable',
            'times.*.lng' => 'nullable',
            'times.*.radius' => 'nullable|numeric',
        ]);

        $appId = $request->app ?? $user->app?->id ?? ($user->studentData->app ?? $user->teacherData->app ?? null);

        DB::beginTransaction();
        try {
            $times = $request->input('times', []);
            $days = $request->input('days', []);
            $firstTime = !empty($times) && is_array($times) ? (array_values($times)[0] ?? []) : [];
            $commonData = [
                'app' => $appId,
                'name' => $request->name,
                'students' => $request->role_target == "karyawan" ? 0 : 1,
                // Simpan data hari pertama sebagai default di tabel lama (backward compat)
                'clock_in_start' => $firstTime['clock_in_start'] ?? null,
                'clock_in_end' => $firstTime['clock_in_end'] ?? null,
                'clock_out_start' => $firstTime['clock_out_start'] ?? null,
                'clock_out_end' => $firstTime['clock_out_end'] ?? null,
                'lat' => $firstTime['lat'] ?? null,
                'lng' => $firstTime['lng'] ?? null,
                'radius' => $firstTime['radius'] ?? 100,
            ];

            $createConfigWithTimes = function ($data) use ($times, $days) {
                $config = AttendanceConfig::create($data);

                // Simpan konfigurasi per hari
                foreach ($times as $index => $timeData) {
                    if (!isset($days[$index])) continue;
                    
                    $config->times()->create([
                        'day' => $days[$index],
                        'clock_in_start' => $timeData['clock_in_start'],
                        'clock_in_end' => $timeData['clock_in_end'],
                        'clock_out_start' => $timeData['clock_out_start'],
                        'clock_out_end' => $timeData['clock_out_end'],
                        'lat' => $timeData['lat'] ?? null,
                        'lng' => $timeData['lng'] ?? null,
                        'radius' => $timeData['radius'] ?? 100,
                    ]);
                }

                return $config;
            };

            if ($request->role_target === 'karyawan' && !empty($request->employee_ids)) {
                foreach ($request->employee_ids as $empId) {
                    $data = $commonData;
                    $data['employee_id'] = $empId;
                    
                    if (!$appId) {
                        $emp = Employee::find($empId);
                        if ($emp && $emp->app_id) {
                            $data['app'] = $emp->app_id;
                        }
                    }

                    $createConfigWithTimes($data);
                }
            } else {
                $commonData['employee_id'] = null;
                $createConfigWithTimes($commonData);
            }

            DB::commit();
            return redirect()->route('dashboard.master.absensi.index')->with('success', 'Konfigurasi berhasil ditambahkan');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $item = AttendanceConfig::with('times')->findOrFail($id);
        $title = "Edit Konfigurasi Absensi";
        $user = Auth::user();
        $apps = [];
        $employees = [];
        $existingConfigs = [];
        $dayList = $this->dayList;

        if ($user->role == 1 && $user->app) {
            $appId = $user->app->id;
            $jabatans = Jabatan::where(function ($q) use ($appId) {
                $q->where('app_id', $appId)->orWhereNull('app_id');
            })->orderBy('name')->get();
            $employees = Employee::with('jabatan')->where('app_id', $appId)->orderBy('name')->get();
            $existingConfigs = AttendanceConfig::with('times')->where('app', $appId)->where('id', '!=', $id)->orderBy('name')->get();
        } else {
            $apps = App::orderBy('name')->get();
            $jabatans = Jabatan::with('app')->orderBy('name')->get();
            $employees = Employee::with('jabatan', 'app')->orderBy('name')->get();
            $existingConfigs = AttendanceConfig::with('times')->where('id', '!=', $id)->orderBy('name')->get();
        }

        return view('master.absensi.form', compact('item', 'title', 'jabatans', 'apps', 'employees', 'existingConfigs', 'dayList'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $isRole0 = $user->role == 0;

        $request->validate([
            'name' => 'required|string|max:255',
            'app' => $isRole0 ? 'nullable|exists:apps,id' : 'nullable',
            'days' => 'required|array|min:1',
            'days.*' => 'required|in:' . implode(',', $this->dayList),
            'times' => 'required|array',
            'times.*.clock_in_start' => 'required',
            'times.*.clock_in_end' => 'required',
            'times.*.clock_out_start' => 'required',
            'times.*.clock_out_end' => 'required',
            'times.*.lat' => 'nullable',
            'times.*.lng' => 'nullable',
            'times.*.radius' => 'nullable|numeric',
        ]);

        DB::beginTransaction();
        try {
            $item = AttendanceConfig::findOrFail($id);

            $times = $request->input('times', []);
            $days = $request->input('days', []);
            $firstTime = !empty($times) && is_array($times) ? (array_values($times)[0] ?? []) : [];
            $data = [
                'name' => $request->name,
                'students' => $request->filled('role_target') ? ($request->role_target == "karyawan" ? 0 : 1) : $item->students,
                // Backward compat: simpan data hari pertama
                'clock_in_start' => $firstTime['clock_in_start'] ?? null,
                'clock_in_end' => $firstTime['clock_in_end'] ?? null,
                'clock_out_start' => $firstTime['clock_out_start'] ?? null,
                'clock_out_end' => $firstTime['clock_out_end'] ?? null,
                'lat' => $firstTime['lat'] ?? null,
                'lng' => $firstTime['lng'] ?? null,
                'radius' => $firstTime['radius'] ?? 100,
            ];

            if ($isRole0 && $request->filled('app')) {
                $data['app'] = $request->app;
            }

            $item->update($data);

            // Hapus semua waktu lama, buat ulang
            $item->times()->delete();

            foreach ($times as $index => $timeData) {
                if (!isset($days[$index])) continue;

                $item->times()->create([
                    'day' => $days[$index],
                    'clock_in_start' => $timeData['clock_in_start'],
                    'clock_in_end' => $timeData['clock_in_end'],
                    'clock_out_start' => $timeData['clock_out_start'],
                    'clock_out_end' => $timeData['clock_out_end'],
                    'lat' => $timeData['lat'] ?? null,
                    'lng' => $timeData['lng'] ?? null,
                    'radius' => $timeData['radius'] ?? 100,
                ]);
            }

            DB::commit();
            return redirect()->route('dashboard.master.absensi.index')->with('success', 'Konfigurasi berhasil diperbarui');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $item = AttendanceConfig::findOrFail($id);
        $item->delete(); // cascade akan hapus times juga
        return redirect()->route('dashboard.master.absensi.index')->with('success', 'Konfigurasi berhasil dihapus');
    }
}
