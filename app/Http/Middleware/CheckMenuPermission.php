<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuPermission
{
    /**
     * Handle an incoming request.
     * Cek apakah user punya permission tertentu berdasarkan RBAC.
     *
     * Usage di route: ->middleware('permission:menu.murid')
     * atau multi:      ->middleware('permission:menu.murid,menu.guru')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  mixed  ...$permissions
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Admin (role 0 / role name 'admin') selalu bypass
        if ($user->hasRoleName('admin')) {
            return $next($request);
        }

        // Jika tidak ada permission yang di-pass, langsung lanjut
        if (empty($permissions)) {
            return $next($request);
        }

        // Cek apakah user punya salah satu dari permissions yang diminta
        if ($user->hasAnyPermission($permissions)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.'
            ], 403);
        }

        return redirect()->route('dashboard.home')
            ->with('err', 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}
