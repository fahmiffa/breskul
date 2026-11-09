<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class isRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  mixed  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $allowedRoles = !empty($roles) ? array_map('intval', $roles) : [0];

        if (!in_array((int) Auth::user()->role, $allowedRoles, true)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.'
                ], 403);
            }
            return redirect()->route('dashboard.home')->with('err', 'Akses ditolak. Halaman ini hanya dapat diakses oleh Admin (Role 0).');
        }

        return $next($request);
    }
}
