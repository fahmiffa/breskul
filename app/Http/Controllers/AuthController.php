<?php
namespace App\Http\Controllers;

use App\Models\Annoucement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.login');
    }

    public function pengumuman($id = null)
    {
        $items = Annoucement::query()
            ->when($id, function ($query) use ($id) {
                // Jika $id ada, ambil 1 data berdasarkan ID
                return $query->where('id', $id);
            }, function ($query) {
                // Jika tidak ada ID, ambil semua data (urutan terbaru)
                return $query->latest();
            })
            ->get();
        return view('auth.pengumuman',compact('items'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if ($user->status != 1) {
                Auth::logout();

                // Hapus session dan CSRF token
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Akun Anda tidak aktif.',
                ]);
            }
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
