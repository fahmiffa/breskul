<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Jabatan;
use App\Models\Teach;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $items = Employee::with(['jabatan', 'user'])
            ->latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app_id', auth()->user()->app->id);
            })
            ->when($request->filled('jabatan_id'), function ($query) use ($request) {
                if ($request->jabatan_id === 'none') {
                    $query->whereNull('jabatan_id');
                } else {
                    $query->where('jabatan_id', $request->jabatan_id);
                }
            })
            ->get();

        $jabatans = Jabatan::where('app_id', auth()->user()->app->id)->orderBy('name', 'asc')->get();
        $title = 'Master Karyawan';
        return view('master.karyawan.index', compact('items', 'title', 'jabatans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $action   = 'Tambah';
        $title    = 'Form Karyawan';
        $jabatans = Jabatan::where('app_id', auth()->user()->app->id)->orderBy('name', 'asc')->get();
        return view('master.karyawan.form', compact('action', 'title', 'jabatans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'name'          => 'required|string|max:255',
                'alamat'        => 'required|string',
                'jenis_kelamin' => 'required|in:1,2',
                'jabatan_id'    => 'nullable|exists:jabatans,id',
                'nomor'         => 'nullable|string|max:20',
                'email'         => 'nullable|email|max:255|unique:users,email',
                'role'          => 'required|in:3,4',
            ],
            [
                'required' => 'Field wajib diisi',
            ]
        );

        DB::beginTransaction();

        try {
            $isGuru = $request->role == 3;

            $userId = DB::table('users')->insertGetId([
                'name'       => $request->name,
                'username'   => UserName($request->name),
                'password'   => Hash::make('binainsantaqwa'),
                'role'       => $request->role,
                'status'     => 1,
                'nomor'      => $request->filled('nomor') ? $request->nomor : null,
                'email'      => $request->filled('email') ? $request->email : null,
                'jabatan_id' => $request->jabatan_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);


            $employee                = new Employee;
            $employee->app_id        = auth()->user()->app->id ?? null;
            $employee->name          = $request->name;
            $employee->alamat        = $request->alamat;
            $employee->jenis_kelamin = $request->jenis_kelamin;
            $employee->user_id       = $userId;
            $employee->jabatan_id    = $request->jabatan_id;
            $employee->save();


            if ($isGuru) {
                $teach          = new Teach;
                $teach->user_id = $userId;
                $teach->name    = $request->name;
                $teach->alamat  = $request->alamat;
                $teach->gender  = $request->jenis_kelamin;
                $teach->app     = auth()->user()->app->id ?? null;
                $teach->save();
            }

            DB::commit();
            return redirect()->route('dashboard.master.karyawan.index')->with('success', 'Berhasil menambahkan karyawan.');
        } catch (\Throwable $e) {
            DB::rollback();
            return back()->withInput()->withErrors('Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $karyawan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $karyawan)
    {
        $karyawan->load('user');
        $action   = 'Edit';
        $title    = 'Form Karyawan';
        $items    = $karyawan;
        $jabatans = Jabatan::where('app_id', auth()->user()->app->id)->orderBy('name', 'asc')->get();
        return view('master.karyawan.form', compact('action', 'title', 'items', 'jabatans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $karyawan)
    {
        $request->validate(
            [
                'name'          => 'required|string|max:255',
                'alamat'        => 'required|string',
                'jenis_kelamin' => 'required|in:1,2',
                'jabatan_id'    => 'nullable|exists:jabatans,id',
                'nomor'         => 'nullable|string|max:20',
                'email'         => [
                    'nullable',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($karyawan->user_id),
                ],
                'role'          => 'required|in:3,4',
            ],
            [
                'required' => 'Field wajib diisi',
            ]
        );

        DB::beginTransaction();

        try {
            $karyawan->name          = $request->name;
            $karyawan->alamat        = $request->alamat;
            $karyawan->jenis_kelamin = $request->jenis_kelamin;
            $karyawan->jabatan_id    = $request->jabatan_id;
            $karyawan->save();

            $isGuru = $request->role == 3;

            if ($isGuru) {
                $teach = Teach::withTrashed()->where('user_id', $karyawan->user_id)->first();
                if ($teach) {
                    if ($teach->trashed()) {
                        $teach->restore();
                    }
                    $teach->name   = $request->name;
                    $teach->alamat = $request->alamat;
                    $teach->gender = $request->jenis_kelamin;
                    $teach->save();
                } else {
                    $teach          = new Teach;
                    $teach->user_id = $karyawan->user_id;
                    $teach->name    = $request->name;
                    $teach->alamat  = $request->alamat;
                    $teach->gender  = $request->jenis_kelamin;
                    $teach->app     = auth()->user()->app->id ?? null;
                    $teach->save();
                }
            } else {
                Teach::where('user_id', $karyawan->user_id)->delete();
            }

            if ($karyawan->user_id) {
                DB::table('users')->where('id', $karyawan->user_id)->update([
                    'name'       => $request->name,
                    'nomor'      => $request->filled('nomor') ? $request->nomor : null,
                    'email'      => $request->filled('email') ? $request->email : null,
                    'role'       => $request->role,
                    'jabatan_id' => $request->jabatan_id,
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('dashboard.master.karyawan.index')->with('success', 'Berhasil mengubah karyawan.');
        } catch (\Throwable $e) {
            DB::rollback();
            return back()->withInput()->withErrors('Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $karyawan)
    {
        Teach::where('user_id', $karyawan->user_id)->delete();
        $karyawan->delete();
        return redirect()->route('dashboard.master.karyawan.index')->with('success', 'Berhasil menghapus karyawan.');
    }

    /**
     * Download template Excel untuk import karyawan.
     */
    public function template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import Karyawan');

        // Header
        $sheet->setCellValue('A1', 'nama');
        $sheet->setCellValue('B1', 'nomor_hp');
        $sheet->setCellValue('C1', 'jenis_kelamin');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '16A34A']],
        ];
        $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);

        // Contoh data
        $sheet->setCellValue('A2', 'Contoh Nama Karyawan');
        $sheet->setCellValue('B2', '08123456789');
        $sheet->setCellValue('C2', 'Laki-laki');

        // Note
        $sheet->setCellValue('A4', 'Catatan: Kolom jenis_kelamin isi dengan: Laki-laki atau Perempuan');

        $writer = new Xlsx($spreadsheet);
        $filename = 'template_import_karyawan.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Import karyawan dari file Excel.
     */
    public function import(Request $request)
    {
        $request->validate([
            'role'       => 'required|in:3,4',
            'jabatan_id' => 'required|exists:jabatans,id',
            'file'       => 'required|mimes:xlsx,xls|max:5120',
        ], [
            'role.required'       => 'Role wajib dipilih.',
            'jabatan_id.required' => 'Jabatan wajib dipilih.',
            'jabatan_id.exists'   => 'Jabatan tidak valid.',
            'file.required'       => 'File Excel wajib diunggah.',
            'file.mimes'          => 'File harus berformat .xlsx atau .xls.',
        ]);

        $jabatan = Jabatan::findOrFail($request->jabatan_id);
        $isGuru  = $request->role == 3;
        $appId   = auth()->user()->app->id ?? null;

        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($request->file('file')->getPathname());
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($request->file('file')->getPathname());
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        } catch (\Exception $e) {
            return back()->withErrors('Gagal membaca file Excel: ' . $e->getMessage());
        }

        $success = 0;
        $errors  = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                // Skip header row
                if ($index == 1) continue;

                $nama   = trim($row['A'] ?? '');
                $hp     = trim($row['B'] ?? '');
                $jenisRaw = strtolower(trim($row['C'] ?? ''));

                if (empty($nama)) continue;

                $jenisKelamin = $jenisRaw === 'laki-laki' || $jenisRaw === 'laki' || $jenisRaw === 'l' ? 1 : 2;

                $userId = DB::table('users')->insertGetId([
                    'name'       => $nama,
                    'username'   => userName($nama),
                    'password'   => Hash::make('breskul'),
                    'role'       => $request->role,
                    'status'     => 1,
                    'nomor'      => $hp ?: null,
                    'jabatan_id' => $jabatan->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $employee = new Employee;
                $employee->app_id       = $appId;
                $employee->name         = $nama;
                $employee->alamat       = '-';
                $employee->jenis_kelamin = $jenisKelamin;
                $employee->user_id      = $userId;
                $employee->jabatan_id   = $jabatan->id;
                $employee->save();

                if ($isGuru) {
                    $teach          = new Teach;
                    $teach->user_id = $userId;
                    $teach->name    = $nama;
                    $teach->alamat  = '-';
                    $teach->gender  = $jenisKelamin;
                    $teach->app     = $appId;
                    $teach->save();
                }

                $success++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('Terjadi kesalahan saat import: ' . $e->getMessage());
        }

        return redirect()->route('dashboard.master.karyawan.index')
            ->with('success', "Berhasil mengimport {$success} karyawan dari jabatan {$jabatan->name}.");
    }
}
