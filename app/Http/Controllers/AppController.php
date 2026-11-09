<?php
namespace App\Http\Controllers;

use App\Models\App;
use App\Models\Feature;
use App\Models\FeatureApp;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AppController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = App::has('user')->with(['user', 'features', 'featureApps.feature'])->latest()->get();
        $title = "Master App";
        return view('master.app.index', compact('items', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $action   = "Tambah";
        $title    = "Form App";
        $features = Feature::orderBy('name', 'asc')->get();
        $selectedFeatures = [];

        return view('master.app.form', compact('action', 'title', 'features', 'selectedFeatures'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "name"          => "required",
            "hp"            => "required|unique:users,nomor",
            "email"         => "required|email|unique:users,email",
            "feature_ids"   => "nullable|array",
            "feature_ids.*" => "exists:features,id",
            "features"      => "nullable|array",
            "features.*"    => "exists:features,id",
        ], [
            'required' => 'Field Wajib diisi',
            'email'    => 'Email tidak valid',
            'unique'   => ':attribute sudah terdaftar',
        ]);

        DB::beginTransaction();

        try {
            $user           = new User;
            $user->name     = $request->name;
            $user->email    = $request->email;
            $user->role     = 1;
            $user->status   = 1;
            $user->nomor    = $request->hp;
            $user->password = Hash::make('rahasia');
            $user->save();

            $items          = new App;
            $items->name    = $request->name;
            $items->user_id = $user->id;
            $items->save();

            // Hubungkan fitur ke app (feature_apps)
            $featureIds = $request->input('feature_ids', $request->input('features', []));
            if (!empty($featureIds)) {
                foreach ($featureIds as $featureId) {
                    FeatureApp::create([
                        'app_id'     => $items->id,
                        'feature_id' => $featureId,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('dashboard.master.app.index')->with('success', 'Berhasil menambahkan data App.');
        } catch (\Throwable $e) {
            DB::rollback();
            return back()->withInput()->withErrors('Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(App $app)
    {
        return redirect()->route('dashboard.master.app.edit', $app->id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(App $app)
    {
        $action           = "Edit";
        $title            = "Form App";
        $items            = $app->load('features', 'featureApps');
        $features         = Feature::orderBy('name', 'asc')->get();
        $selectedFeatures = $app->featureApps->pluck('feature_id')->toArray();

        return view('master.app.form', compact('action', 'title', 'items', 'features', 'selectedFeatures'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, App $app)
    {
        $userId = $app->user->id ?? null;

        $validated = $request->validate([
            'name'          => 'required',
            'hp'            => 'required|unique:users,nomor,' . $userId,
            'email'         => 'required|email|unique:users,email,' . $userId,
            'feature_ids'   => 'nullable|array',
            'feature_ids.*' => 'exists:features,id',
            'features'      => 'nullable|array',
            'features.*'    => 'exists:features,id',
        ], [
            'required' => 'Field wajib diisi',
            'email'    => 'Email tidak valid',
            'unique'   => ':attribute sudah terdaftar',
        ]);

        DB::beginTransaction();

        try {
            $user        = $app->user;
            if ($user) {
                $user->name  = $request->name;
                $user->email = $request->email;
                $user->nomor = $request->hp;
                $user->save();
            }

            $items       = $app;
            $items->name = $request->name;
            $items->save();

            // Sinkronisasi feature_apps (app_id, feature_id)
            $featureIds = $request->input('feature_ids', $request->input('features', []));
            FeatureApp::where('app_id', $app->id)->delete();
            if (!empty($featureIds)) {
                foreach ($featureIds as $featureId) {
                    FeatureApp::create([
                        'app_id'     => $app->id,
                        'feature_id' => $featureId,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('dashboard.master.app.index')->with('success', 'Berhasil memperbarui data App.');
        } catch (\Throwable $e) {
            DB::rollback();
            return back()->withInput()->withErrors('Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(App $app)
    {
        DB::beginTransaction();
        try {
            FeatureApp::where('app_id', $app->id)->delete();
            $user = $app->user;
            $app->delete();
            if ($user) {
                $user->delete();
            }
            DB::commit();
            return redirect()->route('dashboard.master.app.index')->with('success', 'Berhasil menghapus data App.');
        } catch (\Throwable $e) {
            DB::rollback();
            return back()->with('err', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }
}
