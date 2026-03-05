<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Login para obter token JWT. Use o `access_token` retornado no header Authorization (Bearer) nas demais requisições.
     *
     * @unauthenticated
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        /** @var \Tymon\JWTAuth\JWTGuard $guard */
        $guard = auth('api');
        $token = $guard->attempt($credentials);

        if (!$token) {
            return response()->json([
                'message' => 'Credenciais inválidas.',
            ], 401);
        }

        $cookie = cookie(
            'jwt_token',
            $token,
            60,
            null,
            null,
            false,
            true,
            false,
            'Strict'
        );

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $guard->factory()->getTTL() * 60,
        ])->withCookie($cookie);
    }
}
