<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class GestorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Permite acesso a qualquer usuário com cargo_id = 1 (gestor/admin),
     * independente de estar vinculado a uma empresa ou não.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Aceita qualquer usuário com cargo_id = 1 (gestor/admin)
        if ($user && $user->cargo_id === 1) {
            return $next($request);
        }

        abort(403, 'Acesso restrito a gestores.');
    }
}
