<?php

namespace App\Http\Middleware;

use App\Enums\TipoEmpresa;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckTipoEmpresa
{
    /**
     * Verifica se o usuário autenticado pertence a uma empresa do tipo especificado.
     *
     * Uso nas rotas: middleware('tipo:ecommerce') ou middleware('tipo:clinica')
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $tipo  O tipo de empresa requerido (ecommerce, clinica, etc.)
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $tipo): Response
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Não autenticado.');
        }

        // Admin master (cargo_id = 1) sem empresa tem acesso a tudo
        if ($user->cargo_id === 1 && !$user->empresa_id) {
            return $next($request);
        }

        // Verifica se usuário tem empresa
        if (!$user->empresa_id) {
            abort(403, 'Usuário não está vinculado a nenhuma empresa.');
        }

        // Converte string para enum
        $tipoRequerido = TipoEmpresa::tryFrom($tipo);

        if (!$tipoRequerido) {
            abort(500, "Tipo de empresa inválido: {$tipo}");
        }

        // Verifica se a empresa do usuário é do tipo requerido
        if (!$user->isEmpresaTipo($tipoRequerido)) {
            $tipoUsuario = $user->getTipoEmpresa()?->label() ?? 'Desconhecido';
            abort(403, "Acesso negado. Este painel é exclusivo para {$tipoRequerido->label()}. Sua empresa é do tipo: {$tipoUsuario}.");
        }

        return $next($request);
    }
}
