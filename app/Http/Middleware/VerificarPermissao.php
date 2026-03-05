<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarPermissao
{
    /**
     * Verifica se o usuário tem a permissão no painel ativo.
     *
     * Uso nas rotas: middleware('permissao:chat.visualizar') ou middleware('permissao:agenda.criar')
     *
     * @param  string  $permissao  Nome no formato recurso.acao (ex.: chat.visualizar)
     */
    public function handle(Request $request, Closure $next, string $permissao): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Não autenticado.');
        }

        if ($user->cargo_id === 1 && !$user->empresa_id) {
            return $next($request);
        }

        $painelId = session('painel_ativo_id')
            ?? ($user->empresa_id && $user->empresa ? $user->empresa->tipo_painel_id : null);

        if (!$user->temPermissaoNoPainel($permissao, $painelId)) {
            abort(403, 'Sem permissão para esta ação.');
        }

        return $next($request);
    }
}
