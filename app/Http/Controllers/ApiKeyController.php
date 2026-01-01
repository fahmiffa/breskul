<?php
namespace App\Http\Controllers;

use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = ApiKey::latest()->get();
        $title = "Master Api";
        return view('master.api.index', compact('items', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $action = "Tambah";
        $title  = "Form Api";
        return view('master.api.form', compact('action', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $apiKey = APIKey::create([
            'name' => $request->name,
            'key'  => Str::random(32), // Generate API key random 32 karakter
        ]);

        return redirect()->route('dashboard.master.api.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(ApiKey $apiKey)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ApiKey $apiKey)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ApiKey $apiKey)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ApiKey $api)
    {
        $api->delete();
        return redirect()->route('dashboard.master.api.index');
    }
}
