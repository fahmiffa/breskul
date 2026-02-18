<?php

namespace App\Http\Controllers;

use App\Models\Ujian;
use App\Models\Soal;
use App\Models\Mapel;
use App\Models\Teach;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UjianController extends Controller
{
    public function index()
    {
        $teach = Teach::where('user_id', Auth::id())->first();
        if (!$teach) {
            return redirect()->route('dashboard.home')->with('err', 'Data Guru tidak ditemukan.');
        }

        $items = Ujian::where('teach_id', $teach->id)
            ->with(['mapel'])
            ->latest()
            ->get()
            ->map(function ($item) {
                $item->soal_count = is_array($item->soal_id) ? count($item->soal_id) : 0;
                // Fetch soal details for the eye action view modal
                $item->questions = Soal::whereIn('id', $item->soal_id ?? [])->get();
                return $item;
            });

        $title = "Daftar Ujian";
        return view('master.ujian.index', compact('items', 'title'));
    }

    public function create()
    {
        $teach = Teach::where('user_id', Auth::id())->first();
        if (!$teach) return redirect()->back();

        $action = "Tambah";
        $title  = "Buat Ujian Baru";

        // Fetch all subjects that belong to the same app/institution as the teacher
        $mapels = Mapel::where('app', $teach->app)->whereNull('deleted_at')->orderBy('name')->get();

        // Fetch questions created by this teacher
        $soals = Soal::where('teach_id', $teach->id)->get();

        return view('master.ujian.form', compact('action', 'title', 'mapels', 'soals'));
    }

    public function store(Request $request)
    {
        $teach = Teach::where('user_id', Auth::id())->first();

        $request->validate([
            'nama'     => 'required|string|max:255',
            'mapel_id' => 'required|exists:mapels,id',
            'soal_id'  => 'required|array',
            'soal_id.*' => 'exists:soals,id',
        ]);

        Ujian::create([
            'nama'     => $request->nama,
            'mapel_id' => $request->mapel_id,
            'teach_id' => $teach->id,
            'soal_id'  => $request->soal_id,
        ]);

        return redirect()->route('dashboard.master.ujian.index')->with('success', 'Ujian berhasil dibuat.');
    }

    public function edit(Ujian $ujian)
    {
        $teach = Teach::where('user_id', Auth::id())->first();
        if (!$teach || $ujian->teach_id != $teach->id) return redirect()->back();

        $action = "Edit";
        $title  = "Edit Ujian";
        $items  = $ujian;

        // Fetch all subjects that belong to the same app/institution as the teacher
        $mapels = Mapel::where('app', $teach->app)->whereNull('deleted_at')->orderBy('name')->get();
        $soals  = Soal::where('teach_id', $teach->id)->get();

        return view('master.ujian.form', compact('action', 'title', 'items', 'mapels', 'soals'));
    }

    public function update(Request $request, Ujian $ujian)
    {
        $teach = Teach::where('user_id', Auth::id())->first();
        if ($ujian->teach_id != $teach->id) return abort(403);

        $request->validate([
            'nama'     => 'required|string|max:255',
            'mapel_id' => 'required|exists:mapels,id',
            'soal_id'  => 'required|array',
            'soal_id.*' => 'exists:soals,id',
        ]);

        $ujian->update([
            'nama'     => $request->nama,
            'mapel_id' => $request->mapel_id,
            'soal_id'  => $request->soal_id,
        ]);

        return redirect()->route('dashboard.master.ujian.index')->with('success', 'Ujian berhasil diperbarui.');
    }

    public function destroy(Ujian $ujian)
    {
        $teach = Teach::where('user_id', Auth::id())->first();
        if ($ujian->teach_id != $teach->id) return abort(403);

        $ujian->delete();
        return redirect()->route('dashboard.master.ujian.index')->with('success', 'Ujian berhasil dihapus.');
    }
}
