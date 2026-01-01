<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Parse dan autentikasi token
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token invalid'], 401);
        } catch (Exception $e) {
            return response()->json(['error' => 'Token not found'], 401);
        }

        return $next($request);
    }
}
