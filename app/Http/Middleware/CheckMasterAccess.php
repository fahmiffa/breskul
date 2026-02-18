<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMasterAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Jika user adalah Guru (Role 3), mereka dilarang mengakses menu master kecuali Soal.
        // Route::resource('soal', ...) ada di grup master. 
        // Nama route untuk soal biasanya dashboard.master.soal.index, dsb.

        if ($user->role == 3) {
            $routeName = $request->route()->getName();

            // Izinkan akses jika route adalah bagian dari soal atau ujian
            if (str_contains($routeName, 'dashboard.master.soal') || str_contains($routeName, 'dashboard.master.ujian')) {
                return $next($request);
            }

            // Larang akses ke route master lainnya
            return redirect()->route('dashboard.home')->with('error', 'Akses ditolak.');
        }

        return $next($request);
    }
}
