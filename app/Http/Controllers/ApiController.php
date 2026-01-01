<?php
namespace App\Http\Controllers;

use App\Jobs\ProcessFcm;
use App\Models\Annoucement;
use App\Models\ApiKey;
use App\Models\Head;
use App\Models\Present;
use App\Models\Students;
use App\Models\User;
use App\Rules\NumberWa;
use App\Services\Firebase\FirebaseMessage;
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

class ApiController extends Controller
{

    public function forget(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'hp' => ['required', new NumberWa()],
        ],
            [
                'hp.required' => 'Nomor wajib diisi.',
                'hp.unique'   => 'Nomor sudah terdaftar.',
            ]);
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
        $id    = JWTAuth::user()->id;
        $items = Present::whereHas('murid', function ($q) use ($id) {
            $q->where('user', $id);
        })
            ->latest()
            ->get()
            ->map(function ($q) {
                return ["waktu" => $q->time];
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
        $user  = JWTAuth::user()->id;
        $app   = Students::where('user', $user)->first()->app;
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
                        "message" => [
                            "token"        => $be->users->fcm,
                            "notification" => [
                                "title" => "Absensi",
                                "body"  => "Anda berhasil absensi " . $pres->time,
                            ],
                        ],
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
            ->where('role', 2)
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

        return response()->json([
            'success' => true,
            'data'    => [
                'user' => [
                    'id'     => $user->id,
                    'name'   => $user->name,
                    'email'  => $user->email,
                    'role'   => $user->roles,
                    'status' => $user->state,
                    'induk'  => $user->data->nis,
                    'app'    => $user->data->apps->name,
                ],
            ],
        ]);
    }

    public function jadwal()
    {
        $user = Auth::user()->data->id;
        $head = Head::where('status', 0)
            ->where('student_id', $user)
            ->with('kelas.jadwal.time.mapel')
            ->get()
            ->map(function ($item) {

                return [
                    'kelas'  => $item->kelas->name,
                    'jadwal' => $item->kelas->jadwal->map(function ($q) {
                        return [
                            'hari' => $q->hari, 'waktu' => $q->time->map(function($val){
                                return ['start'=>date("H:i",strtotime($val->start)), 'end'=>date("H:i",strtotime($val->end)), 'mapel'=>$val->mapel->name];
                            }),

                        ];
                    }),
                ];

            });

        return response()->json([
            'success' => true,
            'data'    => $head,
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
        return response()->json([
            'success' => true,
            'data'    => [
                "testing", "informasi",
            ],
        ]);
    }

}
