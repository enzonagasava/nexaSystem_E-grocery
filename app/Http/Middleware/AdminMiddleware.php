<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Somente staff Nexa: cargo_id = 1 e sem empresa vinculada
        if ($user && $user->cargo_id === 1 && $user->empresa_id === null) {
            return $next($request);
        }

        abort(403, 'Acesso negado.');
    }
}
