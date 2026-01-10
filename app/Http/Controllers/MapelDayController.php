<?php
namespace App\Http\Controllers;

use App\Models\AcademicYears;
use App\Models\Classes;
use App\Models\Mapel;
use App\Models\MapelDay;
use App\Models\MapelTime;
use App\Models\Teach;
use Illuminate\Http\Request;

class MapelDayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Classes::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })
            ->has('jadwal')
            ->with(['jadwal.time.mapel', 'jadwal.time.mapelteach'])
            ->get();
        $title = "Master Jadwal";
        return view('master.jadwal.index', compact('items', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kelas = Classes::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })
            ->get();

        $mapel = Mapel::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })
            ->get();

        $teach = Teach::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })
            ->get();
        $action = "Tambah";
        $title  = "Form Jadwal";
        return view('master.jadwal.form', compact('action', 'title', 'kelas', 'mapel', 'teach'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kelas'                        => 'required|exists:classes,id',
            'jadwal'                       => 'required|array',
            'jadwal.*.hari'                => 'required',
            'jadwal.*.mapels'              => 'required|array',
            'jadwal.*.mapels.*.mapel'      => 'required|exists:mapels,id',
            'jadwal.*.mapels.*.guru'       => 'required|exists:teaches,id',
            'jadwal.*.mapels.*.start_time' => 'required',
            'jadwal.*.mapels.*.end_time'   => 'required',
        ], [
            'kelas.required'                        => 'Kelas wajib dipilih.',
            'jadwal.*.hari.required'                => 'Hari wajib dipilih.',
            'jadwal.*.mapels.*.mapel.required'      => 'Mata pelajaran wajib dipilih.',
            'jadwal.*.mapels.*.guru.required'       => 'Guru wajib dipilih.',
            'jadwal.*.mapels.*.start_time.required' => 'Waktu mulai wajib diisi.',
            'jadwal.*.mapels.*.end_time.required'   => 'Waktu selesai wajib diisi.',
        ]);

        $akademik = AcademicYears::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })->where('status', 1)
            ->first();

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            foreach ($request->jadwal as $daySchedule) {
                if (isset($daySchedule['mapels'])) {
                    $item           = new MapelDay();
                    $item->class_id = $request->kelas;
                    $item->head     = $akademik->head->id;
                    if (auth()->user()->app) {
                        $item->app = auth()->user()->app->id;
                    }
                    $item->day = $daySchedule['hari'];
                    $item->save();
                    foreach ($daySchedule['mapels'] as $mapel) {
                        $maptime              = new MapelTime;
                        $maptime->mapelday_id = $item->id;
                        $maptime->mapel_id    = $mapel['mapel'];
                        $maptime->teacher_id  = $mapel['guru'];
                        $maptime->start       = $mapel['start_time'];
                        $maptime->end         = $mapel['end_time'];
                        $maptime->save();

                    }
                }
            }

            \Illuminate\Support\Facades\DB::commit();
            return redirect()->route('dashboard.master.jadwal.index')->with('success', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->withErrors($e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MapelDay $mapelDay)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $items = Classes::findOrFail($id);

        $kelas = Classes::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })
            ->get();

        $mapel = Mapel::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })
            ->get();

        $teach = Teach::latest()
            ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                $query->where('app', auth()->user()->app->id);
            })
            ->get();

        $jadwals = MapelDay::where('class_id', $id)->with('time')->get()->map(function ($day) {
            return [
                'hari'   => $day->day,
                'mapels' => $day->time->map(function ($t) {
                    return [
                        'id'         => $t->id,
                        'mapel_id'   => $t->mapel_id,
                        'guru'       => $t->teacher_id,
                        'start_time' => $t->start,
                        'end_time'   => $t->end,
                    ];
                }),
            ];
        });

        $action = "Edit";
        $title  = "Form Jadwal";
        return view('master.jadwal.form', compact('action', 'title', 'items', 'mapel', 'kelas', 'teach', 'jadwals'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'kelas'                        => 'required|exists:classes,id',
            'jadwal'                       => 'required|array',
            'jadwal.*.hari'                => 'required',
            'jadwal.*.mapels'              => 'required|array',
            'jadwal.*.mapels.*.mapel'      => 'required|exists:mapels,id',
            'jadwal.*.mapels.*.guru'       => 'required|exists:teaches,id',
            'jadwal.*.mapels.*.start_time' => 'required',
            'jadwal.*.mapels.*.end_time'   => 'required',
        ], [
            'kelas.required'                        => 'Kelas wajib dipilih.',
            'jadwal.*.hari.required'                => 'Hari wajib dipilih.',
            'jadwal.*.mapels.*.mapel.required'      => 'Mata pelajaran wajib dipilih.',
            'jadwal.*.mapels.*.guru.required'       => 'Guru wajib dipilih.',
            'jadwal.*.mapels.*.start_time.required' => 'Waktu mulai wajib diisi.',
            'jadwal.*.mapels.*.end_time.required'   => 'Waktu selesai wajib diisi.',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $akademik = AcademicYears::latest()
                ->when(auth()->user()->role == 1 && auth()->user()->app, function ($query) {
                    $query->where('app', auth()->user()->app->id);
                })->where('status', 1)
                ->first();

            // Find existing days for this class and delete them (and their times)
            $existingDays = MapelDay::where('class_id', $id)->get();
            foreach ($existingDays as $day) {
                // Manually delete related times to be safe
                MapelTime::where('mapelday_id', $day->id)->delete();
                $day->delete();
            }

            foreach ($request->jadwal as $daySchedule) {
                if (isset($daySchedule['mapels'])) {
                    $item           = new MapelDay();
                    $item->class_id = $request->kelas;
                    if (auth()->user()->app) {
                        $item->app = auth()->user()->app->id;
                    }
                    $item->head = $akademik->head->id;
                    $item->day  = $daySchedule['hari'];
                    $item->save();
                    foreach ($daySchedule['mapels'] as $mapel) {
                        $maptime              = new MapelTime;
                        $maptime->mapelday_id = $item->id;
                        $maptime->mapel_id    = $mapel['mapel'];
                        $maptime->teacher_id  = $mapel['guru'];
                        $maptime->start       = $mapel['start_time'];
                        $maptime->end         = $mapel['end_time'];
                        $maptime->save();

                    }
                }
            }

            \Illuminate\Support\Facades\DB::commit();
            return redirect()->route('dashboard.master.jadwal.index')->with('success', 'Data berhasil diperbarui');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->withErrors($e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MapelDay $mapelDay)
    {
        //
    }
}
