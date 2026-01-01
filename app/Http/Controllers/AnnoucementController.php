<?php
namespace App\Http\Controllers;

use App\Models\Annoucement;
use Illuminate\Http\Request;

class AnnoucementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Annoucement::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })
            ->latest()
            ->get();
        $title = "Pengumman";
        return view('home.pengumuman.index', compact('items', 'title', 'items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $action = "Tambah";
        $title  = "Form Pengumman";
        return view('home.pengumuman.form', compact('action', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'content' => 'required|string',
        ]);

        if ($request->hasFile('image')) {
            $validated['img'] = $request->file('image')->store('pengumuman', 'public');
        }

        $res       = new Annoucement;
        $res->name = $validated['name'];
        $res->app  = auth()->user()->app->id;
        $res->img  = $validated['img'] ?? null;
        $res->des  = $validated['content'];
        $res->save();

        return redirect()->route('dashboard.pengumuman.index')
            ->with('success', 'Pengumuman berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Annoucement $pengumuman)
    {
        $items  = $pengumuman;
        $action = "Edit";
        $title  = "Form Pengumman";
        return view('home.pengumuman.form', compact('action', 'title', 'items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Annoucement $pengumuman)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'content' => 'required|string',
        ]);

        $res = $pengumuman;
        if ($request->hasFile('image')) {
            $validated['img'] = $request->file('image')->store('pengumuman', 'public');
            $res->img         = $validated['img'] ?? null;
        }

        $res->name = $validated['name'];
        $res->des  = $validated['content'];
        $res->save();

        return redirect()->route('dashboard.pengumuman.index')
            ->with('success', 'Pengumuman berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Annoucement $annoucement)
    {
        //
    }
}
