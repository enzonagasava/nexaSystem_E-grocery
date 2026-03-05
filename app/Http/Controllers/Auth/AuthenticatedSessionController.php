<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AuthenticatedSessionController extends Controller
{
    /**
     * Realiza login e retorna token JWT.
     */
    public function login(Request $request)
    {
        if (auth()->check()) {
        // Usuário já está autenticado, redireciona para dashboard
        return redirect('/');
    }

        return Inertia::render('auth/Login', [
            'canResetPassword' => ('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }


    public function store(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $token = auth()->attempt($credentials);

        if (!$token) {
            // Retorne erro Inertia válido (pode ser com redirect back com erros)
            return back()->withErrors(['email' => 'Credenciais inválidas']);
        }

        $user = Auth::user();

        // Carregar relacionamento empresa para determinar o tipo
<<<<<<< HEAD
        $user->empresa;
=======
        $user->load('empresa');
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f

        // Criar cookie HTTP-only com o token JWT
        $cookie = cookie(
            'jwt_token',      // nome do cookie
            $token,           // valor do token
            60,               // tempo de vida em minutos
            null,             // path (default '/')
            null,             // domain (default)
            false,            // secure (false para localhost)
            true,             // httpOnly
            false,            // raw
            'Strict'          // SameSite
        );

        // Usa o método getDashboardRoute() do User para determinar o redirecionamento
        // baseado no tipo da empresa (ecommerce, clinica) ou cargo (admin, cliente)
        $redirectUrl = route($user->getDashboardRoute());

        // Retornar redirecionamento Inertia com cookie
        return Inertia::location($redirectUrl)->withCookie($cookie);
    }


    /**
     * Retorna dados do usuário autenticado.
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    /** 
     * Realiza logout invalidando o token JWT.
     */
    public function logout(Request $request): RedirectResponse
    {
        // Se estiver usando JWT
        if (auth()->guard('api')->check()) {
            auth()->guard('api')->logout();
        }
        
        // Se estiver usando sessão web
        Auth::guard('web')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Remover cookie JWT
        $cookie = Cookie::forget('jwt_token', '/');
        
        return redirect('/')->withCookie($cookie);
    }

    /**
     * Formata a resposta com token JWT.
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}
