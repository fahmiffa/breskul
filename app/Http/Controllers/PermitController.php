<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PermitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = \App\Models\Permit::with('employee')->latest()->get();
        return view('master.izin.index', compact('items'));
    }

    public function update(Request $request, string $id)
    {
        $permit = \App\Models\Permit::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:0,1,2,3'
        ]);

        $permit->update(['status' => $request->status]);

        return back()->with('success', 'Status izin berhasil diupdate');
    }
}
