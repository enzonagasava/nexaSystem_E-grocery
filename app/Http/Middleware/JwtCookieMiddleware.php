<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtCookieMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($token = $request->cookie('jwt_token')) {
            try {
                JWTAuth::setToken($token);
                $user = JWTAuth::authenticate();
                auth()->guard('api')->setUser($user);
            } catch (\Exception $e) {
                // Token inválido ou expirado
                // Opcional: limpar cookie ou fazer logout
            }
        }

        return $next($request);
    }
}
