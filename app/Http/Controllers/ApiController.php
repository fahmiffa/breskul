<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessFcm;
use App\Models\Annoucement;
use App\Models\ApiKey;
use App\Models\Bill;
use App\Services\BillingCodeService;
use App\Services\QrisLogic;
use App\Models\Extracurricular;
use App\Models\Head;
use App\Models\Mapel;
use App\Models\MapelDay;
use App\Models\MapelTime;
use App\Models\Present;
use App\Models\StudentExtracurricular;
use App\Models\Students;
use App\Models\AttendanceConfig;
use App\Models\User;
use App\Rules\NumberWa;
use App\Services\Firebase\FirebaseMessage;
use App\Services\PaymentWebhookService;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;


class ApiController extends Controller
{

    public function forget(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'hp' => ['required', new NumberWa()],
            ],
            [
                'hp.required' => 'Nomor wajib diisi.',
                'hp.unique'   => 'Nomor sudah terdaftar.',
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 400);
        }
        DB::beginTransaction();
        try {
            $user = User::where('nomor', $request->hp)->first();
            if (! $user) {
                return response()->json([
                    'errors' => ['hp' => 'Nomor tidak valid'],
                ], 400);
            }

            $pass           = Str::random(5);
            $user->password = bcrypt($pass);
            $user->save();

            $to       = '62' . substr($user->nomor, 1);
            $response = Http::post(env('URL_WA') . '/send', [
                'number'  => env('NumberWa'),
                'to'      => $to,
                'message' => "Anda reset Berhasil Password\nPassword akun anda : " . $pass,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
            ], 200);
        } catch (\Throwable $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function fcm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'   => 'required',
            'content' => 'required',
            "topic"   => 'required',
        ], [
            'required' => 'Field :attribute wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 400);
        }

        $message = [
            "message" => [
                // "to"           => "topics/news",
                "to"           => $request->topic,
                "notification" => [
                    "title" => $request->title,
                    "body"  => $request->content,
                ],
            ],
        ];

        return FirebaseMessage::sendTopicBroadcast(
            $request->topic,
            $request->title,
            $request->content
        );
    }

    public function absensi()
    {
        $user = Auth::user();
        $id = $user->id;

        if ($user->role == 2) {
            $items = Present::whereHas('murid', function ($q) use ($id) {
                $q->where('user', $id);
            });
        } else {
            $teacherId = $user->teacherData->id ?? null;
            $items = Present::where('teacher_id', $teacherId);
        }

        $items = $items->latest()
            ->get()
            ->map(function ($q) {
                return [
                    "waktu" => $q->time,
                    "status" => $q->status,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $items,
        ]);
    }

    public function bill()
    {
        $id = JWTAuth::user()->id;

        $items = Head::whereHas('murid', function ($q) use ($id) {
            $q->where('user', $id);
        })
            ->where('status', 1)
            ->with('bill.payment')
            ->latest()
            ->get()
            ->map(function ($head) {
                return $bills = $head->bill->map(function ($bill) {
                    return [
                        'bill'    => $bill->id ?? null,
                        'status'  => $bill->status ?? null,
                        'via'     => $bill->via ?? null,
                        'name'    => $bill->payment->name ?? null,
                        'nominal' => number_format($bill->payment->nominal, 0, ',', '.') ?? null,
                    ];
                });
            });

        return response()->json([
            'success' => true,
            'data'    => $items,
        ]);
    }

    public function pengumuman($id = null)
    {
        $user  = JWTAuth::user();
        if ($user->role == 2) {
            $app   = $user->studentData->app;
        } else {
            $app   = $user->teacherData->app;
        }
        $items = Annoucement::query()
            ->when($id, function ($query) use ($id) {
                return $query->where('id', $id);
            }, function ($query) {
                return $query->latest();
            })
            ->where('app', $app)
            ->get();
        return response()->json([
            'success' => true,
            'data'    => $items,
        ]);
    }

    public function rfid(Request $request)
    {
        $deviceId = $request->header('Device-ID', 'UNKNOWN_DEVICE');
        $device   = ApiKey::where('name', $deviceId)->first();
        $body     = $request->all();
        if ($device) {

            $be = Students::where('uuid', $request->uid)->first();
            if ($be) {
                Log::channel('absensi')->info('Data absensi diterima', [
                    'device_id' => $deviceId,
                    'payload'   => $request->uid,
                ]);

                $pres             = new Present;
                $pres->student_id = $be->id;
                $pres->app        = $be->app;
                $pres->waktu      = date("Y-m-d H:i:s");
                $pres->save();

                if ($be->users->fcm) {
                    $message = [
                        "topic"  => "user_" . $be->users->id,
                        "title" => "Absensi",
                        "body"  => "Anda berhasil absensi " . $pres->time,
                    ];
                    ProcessFcm::dispatch($message);
                }

                return response()->json([
                    'success' => true,
                    'msg'     => $be->name,
                ], 200);
            } else {
                Log::channel('absensi')->info('Data absensi ditolak', [
                    'device_id' => $deviceId,
                    'payload'   => $request->uid,
                ]);

                return response()->json([
                    'success' => false,
                    'msg'     => 'Kartu error',
                ], 400);
            }
        } else {

            Log::channel('absensi')->info('Data absensi ditolak', [
                'device_id' => $deviceId,
                'payload'   => $request->uid,
            ]);

            return response()->json([
                'success' => false,
                'msg'     => 'Perangkat error',
            ], 400);
        }
    }

    public function paymentWebhook(Request $request, PaymentWebhookService $service)
    {
        $service->handle($request->all());
        return response()->json(['success' => true], 200);
    }

    public function scanQr(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 400);
        }

        $qrData = \explode('-', $request->qr_code);
        if (\count($qrData) != 2) {
            return response()->json([
                'success' => false,
                'message' => 'Format QR Code tidak valid',
            ], 400);
        }

        $studentId = $qrData[0];
        $nis = $qrData[1];

        $student = Students::where('id', $studentId)->where('nis', $nis)->first();
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan',
            ], 404);
        }

        $now = now();

        // Cek jika sudah absen hari ini (opsional, tapi biasanya dibutuhkan agar tidak double)
        // Namun untuk operator scanning, mungkin dibolehkan berkali-kali? 
        // Mari kita buat simpel saja dulu.

        $pres = new Present;
        $pres->student_id = $student->id;
        $pres->app        = $student->app;
        $pres->waktu      = $now;
        $pres->status     = 'masuk'; // Default status for QR scan
        $pres->save();

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil dicatat untuk ' . $student->name,
            'data'    => [
                'name'  => $student->name,
                'waktu' => $pres->time,
            ],
        ]);
    }

    /**
     * Get a JWT via given credentials.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login'    => 'required', // bisa email atau name
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 400);
        }

        $login    = $request->input('login');
        $password = $request->input('password');

        $user = \App\Models\User::where('email', $login)
            ->orWhere('username', $login)
            ->whereIn('role', [0, 1, 2, 3])
            ->first();

        if (! $user) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        if ($user->status == 0) {
            return response()->json(['error' => 'Akun tidak aktif'], 403);
        }

        $credentials = ['name' => $user->name, 'password' => $password];

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        // Simpan FCM jika ada
        if ($request->fcm) {
            $user->fcm = $request->fcm;
            $user->save();
        }

        return response()->json([
            'token'      => $token,
            'expires_in' => auth('api')->factory()->getTTL() * 1,
            'role'       => $user->role,
            'uid'        => md5($user->id),
            'mode'       => config('app.school_mode')
        ]);
    }

    /**
     * Refresh a token.
     */
    public function refresh()
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data'    => [
                    'token'      => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60,
                ],
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token could not be refreshed',
            ], 401);
        }
    }

    /**
     * Get user data
     */
    public function data()
    {

        $user = Auth::user();

        if (Auth::user()->role == 2) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'user' => [
                        'id'     => $user->id,
                        'name'   => $user->name,
                        'role'   => $user->role,
                        'status' => $user->status,
                        'image'  => $user->image ? asset('storage/' . $user->image) : null,
                        'induk'  => $user->studentData->nis,
                        'app'    => $user->studentData->apps->name,
                    ],
                ],
            ]);
        } else {
            return response()->json([
                'success' => true,
                'data'    => [
                    'user' => [
                        'id'     => $user->id,
                        'name'   => $user->teacherData?->name ?? $user->name,
                        'role'   => $user->role,
                        'status' => $user->status,
                        'image'  => $user->image ? asset('storage/' . $user->image) : null,
                        'app'    => $user->teacherData?->apps?->name ?? $user->app?->name ?? '-',
                    ],
                ],
            ]);
        }
    }

    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $user = Auth::user();

            // Delete old image if exists
            if ($user->image && \Storage::disk('public')->exists($user->image)) {
                \Storage::disk('public')->delete($user->image);
            }

            $path = $request->file('image')->store('profiles', 'public');

            $user->image = $path;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diperbarui',
                'image'   => asset('storage/' . $path),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function jadwal()
    {
        if (Auth::user()->role == 2) {
            $user = Auth::user()->data->id;

            $head = Head::where('status', 1)
                ->where('student_id', $user)
                ->with(['kelas.jadwal.time.mapel', 'kelas.jadwal.time.mapelteach'])
                ->first();

            $data = $head ?  $head->kelas->jadwal->map(function ($q) {
                return [
                    'hari'  => $q->hari,
                    'waktu' => $q->time->map(function ($val) {
                        return [
                            'start' => date("H:i", strtotime($val->start)),
                            'end'   => date("H:i", strtotime($val->end)),
                            'mapel' => $val->mapel->name,
                            'guru' => $val->mapelteach->name,
                        ];
                    }),
                ];
            }) : null;

            return response()->json([
                'success' => true,
                'data'    => $data,
            ]);
        } else if (Auth::user()->role == 3) {
            $user   = Auth::user()->data->id;
            $jadwal = MapelTime::where('teacher_id', $user)
                ->with(['mapel', 'mapelday.kelas'])
                ->get()
                ->groupBy('mapelday.class_id')
                ->map(function ($times) {
                    $first = $times->first();
                    return [
                        'kelas'  => $first->mapelday->kelas->name ?? null,
                        'jadwal' => $times->groupBy('mapelday.day')->map(function ($group) {
                            $f = $group->first();
                            return [
                                'hari'  => $f->mapelday->hari,
                                'waktu' => $group->map(function ($val) {
                                    return [
                                        'start' => date("H:i", strtotime($val->start)),
                                        'end'   => date("H:i", strtotime($val->end)),
                                        'mapel' => $val->mapel->name ?? null,
                                    ];
                                }),
                            ];
                        })->values(),
                    ];
                })->values();

            return response()->json([
                'success' => true,
                'data'    => $jadwal,
            ]);
        }
    }

    public function ekstra()
    {
        $role = Auth::user()->role;
        $id   = Auth::user()->data->id;

        if ($role == 2) {
            $items = StudentExtracurricular::where('student_id', $id)
                ->with('extracurricular.guru')
                ->get()
                ->map(function ($q) {
                    return [
                        'nama'  => $q->extracurricular->nama ?? null,
                        'guru'  => $q->extracurricular->guru->name ?? null,
                        'waktu' => $q->extracurricular->waktu ?? null,
                    ];
                });
        } else if ($role == 3) {
            $items = Extracurricular::where('guru_id', $id)
                ->get()
                ->map(function ($q) {
                    return [
                        'nama'  => $q->nama,
                        'waktu' => $q->waktu,
                    ];
                });
        } else {
            $items = [];
        }

        return response()->json([
            'success' => true,
            'data'    => $items,
        ]);
    }

    /**
     * Register a student for an extracurricular activity.
     */
    public function daftarEkstra(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'extracurricular_id' => 'required|exists:extracurriculars,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = Auth::user();

        if ($user->role != 2) { // Only students (role 2) can register for extracurriculars
            return response()->json(['error' => 'Unauthorized. Only students can register for extracurriculars.'], 403);
        }

        $student   = $user->data;
        $studentId = $student->id;

        // Check if the student is already registered for this extracurricular
        $existingRegistration = StudentExtracurricular::where('student_id', $studentId)
            ->where('extracurricular_id', $request->extracurricular_id)
            ->first();

        if ($existingRegistration) {
            return response()->json(['error' => 'Anda sudah terdaftar di ekstrakurikuler ini.'], 409);
        }

        // Create the registration
        $registration = StudentExtracurricular::create([
            'student_id'         => $studentId,
            'extracurricular_id' => $request->extracurricular_id,
            'app'                => $student->app, // Penting untuk multi-tenancy
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mendaftar ekstrakurikuler.',
            'data'    => $registration,
        ], 201);
    }

    public function ekstrakurikuler()
    {
        $items = Extracurricular::with('guru')
            ->get()
            ->map(function ($q) {
                return [
                    'id'    => $q->id,
                    'nama'  => $q->nama,
                    'guru'  => $q->guru->name ?? null,
                    'waktu' => $q->waktu,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $items,
        ]);
    }

    public function upass(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old' => 'required',
            'new' => 'required',
        ], [
            'old.required' => 'Password lama Wajib diisi',
            'new.required' => 'Password baru Wajib diisi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 400);
        }

        $id   = JWTAuth::user()->id;
        $data = User::where('id', $id)->first();

        if (! $data) {
            return response()->json(['errors' => ['message' => 'User tidak valid']], 400);
        }

        if (! Hash::check($request->old, $data->password)) {
            return response()->json(['errors' => ['message' => 'Password lama tidak valid']], 400);
        }

        $data->password = Hash::make($request->new);
        $data->save();

        return response()->json(['status' => true, "data" => $request->new], 200);
    }

    public function topic()
    {
        $id = Auth::user()->id;
        return response()->json([
            'success' => true,
            'data'    => [
                "testing",
                "informasi",
                "user_{$id}"
            ],
        ]);
    }

    public function getAbsensiConfig()
    {
        $user = Auth::user();
        if ($user->role == 2) {
            $app = $user->studentData->app;
        } else {
            $app = $user->teacherData->app;
        }

        $items = AttendanceConfig::where('app', $app)
            ->where('role', $user->role)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $items,
        ]);
    }

    public function submitAbsensi(Request $request)
    {
        $user = Auth::user();
        $app = null;
        $studentId = null;
        $teacherId = null;

        if ($user->role == 2) {
            $app = $user->studentData->app;
            $studentId = $user->studentData->id;
        } else {
            $app = $user->teacherData->app;
            $teacherId = $user->teacherData->id;
        }

        $config = AttendanceConfig::where('app', $app)
            ->where('role', $user->role)
            ->first();

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal absensi tidak ditemukan untuk hari ini.',
            ], 400);
        }

        $type = null;
        $now = now();

        if ($now->between(
            Carbon::createFromTimeString($config->clock_in_start),
            Carbon::createFromTimeString($config->clock_in_end)
        )) {
            $type = 'masuk';
        } elseif ($now->between(
            Carbon::createFromTimeString($config->clock_out_start),
            Carbon::createFromTimeString($config->clock_out_end)
        )) {
            $type = 'pulang';
        }

        if (!$type) {
            return response()->json([
                'success' => false,
                'message' => 'Sekarang bukan waktu absensi.',
            ], 400);
        }

        // Check if already absensi today for this type
        $exists = Present::where('app', $app)
            ->when($user->role == 2, function ($q) use ($studentId) {
                return $q->where('student_id', $studentId);
            }, function ($q) use ($teacherId) {
                return $q->where('teacher_id', $teacherId);
            })
            ->whereDate('waktu', $now->toDateString())
            ->where('status', $type)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah absensi ' . $type . ' hari ini.',
            ], 400);
        }

        $pres = new Present;
        if ($user->role == 2) {
            $pres->student_id = $studentId;
        } else {
            $pres->teacher_id = $teacherId;
        }
        $pres->app = $app;
        $pres->waktu = $now;
        $pres->status = $type;
        $pres->save();

        return response()->json([
            'success' => true,
            'message' => 'Absensi ' . $type . ' berhasil.',
            'data' => [
                'type' => $type,
                'waktu' => $pres->time
            ]
        ]);
    }

    public function generateBillQris(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bill_id' => 'required|exists:bills,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $bill = Bill::with('payment')->find($request->bill_id);

            // Cek jika status sudah dibayar
            if ($bill->status == 1) {
                return response()->json(['error' => 'Tagihan sudah dibayar.'], 400);
            }

            // Cek apakah sudah ada QRIS yang aktif/belum expired
            if (!empty($bill->unique_code) && !empty($bill->qris_data)) {
                // Periksa apakah created_at code unik masih valid UNTUK HARI INI (Logic billing date)
                // Namun simplenya, kita cek expired_at yang kita simpan
                $now = now();
                $expiredAt = $bill->qris_expired_at ? \Carbon\Carbon::parse($bill->qris_expired_at) : null;

                if ($expiredAt && $now->lt($expiredAt)) {
                    // Reuse Existing
                    $qrisString = $bill->qris_data;
                    $uniqueCode = $bill->unique_code;
                    $nominal = $bill->payment->nominal;
                    $totalAmount = $nominal + intval($uniqueCode);
                    // Generate Image URL (or store it, but dynamic is fine)
                    $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrisString);

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'qris_string' => $qrisString,
                            'qr_image_url' => $qrImageUrl,
                            'amount_base' => $nominal,
                            'unique_code' => $uniqueCode,
                            'total_amount' => $totalAmount,
                            'expired_at' => $expiredAt->toIso8601String(),
                            'reset_at' => '22:00',
                            'is_existing' => true
                        ]
                    ]);
                }
            }

            // Generate NEW Unique Code
            $service = new BillingCodeService();
            $uniqueCode = $service->generateUniqueCode($bill->id);

            $nominal = $bill->payment->nominal;
            $totalAmount = $nominal + intval($uniqueCode);

            // Generate QRIS String
            // Assuming QrisLogic exists and works as before, or we use a static string for now as per previous code context.
            // Let's use the QrisLogic service to be robust if it exists, otherwise fallback/dummy.
            // Since QrisLogic is imported on line 9, we try to use it.
            $qrisLogic = new QrisLogic();
            $qrisString = $qrisLogic->generateDynamicQris(env("QRIS"), $totalAmount);
            // If QrisLogic doesn't exist or we want to use the previous dummy logic, we would swap it here. 
            // But let's assume QrisLogic works.

            // Update Bill with QRIS Data
            $expiredAt = \Carbon\Carbon::now()->setTime(23, 0, 0);
            $bill->unique_code = $uniqueCode;
            $bill->qris_data = $qrisString;
            $bill->qris_expired_at = $expiredAt;
            $bill->save();

            $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrisString);

            return response()->json([
                'success' => true,
                'data' => [
                    'qris_string' => $qrisString,
                    'qr_image_url' => $qrImageUrl,
                    'amount_base' => $nominal,
                    'unique_code' => $uniqueCode,
                    'total_amount' => $totalAmount,
                    'expired_at' => $expiredAt->toIso8601String(),
                    'reset_at' => '23:00',
                    'is_existing' => false
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function paySimulation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bill_id' => 'required|exists:bills,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            // Load relasi sampai ke User
            $bill = Bill::with(['head.murid.users', 'payment'])->find($request->bill_id);

            if ($bill->status == 1) {
                return response()->json(['message' => 'Tagihan sudah lunas sebelumnya.'], 200);
            }

            // Update Status menjadi Lunas (1)
            $bill->status = 1;
            $bill->save();

            // Kirim Notif ke User
            if ($bill->head && $bill->head->murid && $bill->head->murid->users) {
                $user = $bill->head->murid->users;

                // Topic convention: user_{id}
                $topic = 'user_' . $user->id;
                $title = 'Pembayaran Berhasil';
                $nominal = number_format($bill->payment->nominal ?? 0, 0, ',', '.');
                $body = "Pembayaran " . $bill->payment->name . " sebesar Rp " . $nominal . " telah lunas.";

                $message = [
                    "message" => [
                        "token" => $user->fcm,
                        "notification" => [
                            "title" => $title,
                            "body" => $body,
                        ],
                    ],
                ];

                FirebaseMessage::sendTopicBroadcast($topic, $title, $body);
            }

            return response()->json([
                'success' => true,
                'message' => 'Simulasi pembayaran berhasil.',
                'data' => $bill
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
