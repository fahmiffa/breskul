<?php

namespace App\Http\Controllers;

use App\Models\Soal;
use App\Imports\SoalImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class SoalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role != 3) {
            return redirect()->route('dashboard.home')->with('error', 'Akses ditolak.');
        }

        $teachId = $user->teacherData->id ?? null;

        // Get all exams by this teacher to check question usage
        $exams = \App\Models\Ujian::where('teach_id', $teachId)->get();

        $items = Soal::where('teach_id', $teachId)->latest()->get()->map(function ($soal) use ($exams) {
            $usedIn = $exams->filter(function ($exam) use ($soal) {
                return is_array($exam->soal_id) && in_array($soal->id, $exam->soal_id);
            })->pluck('nama')->toArray();

            $soal->used_in_exams = $usedIn;
            return $soal;
        });

        $title = "Master Soal";

        return view('master.soal.index', compact('items', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $action = "Tambah";
        $title = "Tambah Soal";
        return view('master.soal.form', compact('action', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $teachId = $user->teacherData->id ?? null;

        if (!$teachId) {
            return back()->with('error', 'Data Guru tidak ditemukan.');
        }

        $rules = [
            'nama'   => 'required',
            'tipe'   => 'required|in:Pilihan ganda,Isian',
            'jawaban' => 'required',
        ];

        if ($request->tipe == 'Pilihan ganda') {
            $rules['opsi_a'] = 'required';
            $rules['opsi_b'] = 'required';
            $rules['opsi_c'] = 'required';
            $rules['opsi_d'] = 'required';
            $rules['opsi_e'] = 'required';
        }

        $validated = $request->validate($rules);
        $validated['teach_id'] = $teachId;

        Soal::create($validated);

        return redirect()->route('dashboard.master.soal.index')->with('success', 'Soal berhasil disimpan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Soal $soal)
    {
        $action = "Edit";
        $title  = "Edit Soal";
        $items  = $soal;
        return view('master.soal.form', compact('action', 'title', 'items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Soal $soal)
    {
        $rules = [
            'nama'   => 'required',
            'tipe'   => 'required|in:Pilihan ganda,Isian',
            'jawaban' => 'required',
        ];

        if ($request->tipe == 'Pilihan ganda') {
            $rules['opsi_a'] = 'required';
            $rules['opsi_b'] = 'required';
            $rules['opsi_c'] = 'required';
            $rules['opsi_d'] = 'required';
            $rules['opsi_e'] = 'required';
        }

        $validated = $request->validate($rules);

        // Clear opsi if tipe changed to Isian
        if ($request->tipe == 'Isian') {
            $validated['opsi_a'] = null;
            $validated['opsi_b'] = null;
            $validated['opsi_c'] = null;
            $validated['opsi_d'] = null;
            $validated['opsi_e'] = null;
        }

        $soal->update($validated);

        return redirect()->route('dashboard.master.soal.index')->with('success', 'Soal berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Soal $soal)
    {
        $soal->delete();
        return redirect()->route('dashboard.master.soal.index')->with('success', 'Soal berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $user = Auth::user();
        $teachId = $user->teacherData->id ?? null;

        if (!$teachId) {
            return back()->with('error', 'Data Guru tidak ditemukan.');
        }

        try {
            Excel::import(new SoalImport($teachId), $request->file('file'));
            return redirect()->route('dashboard.master.soal.index')->with('success', 'Soal berhasil diimport.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat mengimport: ' . $e->getMessage());
        }
    }

    public function template()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=template_soal.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['No', 'Pertanyaan', 'Tipe', 'Opsi A', 'Opsi B', 'Opsi C', 'Opsi D', 'Opsi E', 'Jawaban'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            // Example row
            fputcsv($file, ['1', 'Siapakah penemu lampu pijar?', 'Pilihan ganda', 'Albert Einstein', 'Thomas Alva Edison', 'Isaac Newton', 'Nikola Tesla', 'Galileo Galilei', 'Thomas Alva Edison']);
            fputcsv($file, ['2', 'Ibu kota Indonesia adalah...', 'Isian', '', '', '', '', '', 'Jakarta']);

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
