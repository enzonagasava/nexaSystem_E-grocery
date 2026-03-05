<?php

namespace App\Http\Middleware;

use App\Enums\TipoEmpresa;
<<<<<<< HEAD
use App\Models\TipoPainel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Inertia\Inertia;
=======
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f

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
<<<<<<< HEAD
    {    
=======
    {
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
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
<<<<<<< HEAD
            Log::warning('CheckTipoEmpresa: usuário sem empresa tentando acessar painel por tipo.', [
                'user_id' => $user->id ?? null,
                'cargo_id' => $user->cargo_id ?? null,
                'required_tipo' => $tipo,
            ]);

            abort(403, 'Usuário não está vinculado a nenhuma empresa.');
        }

        if (!$tipo) {
            Log::error('CheckTipoEmpresa: tipo de empresa inválido no middleware.', ['received' => $tipo]);
            abort(500, "Tipo de empresa inválido: {$tipo}");
        }
        // Verifica se a empresa do usuário é do tipo requerido
        if (!$user->isEmpresaTipo($tipo)) {
            $empresaTipo = $user->getTipoEmpresa($tipo);
            Log::warning('CheckTipoEmpresa: acesso negado por tipo de empresa.', [
                'user_id' => $user->id ?? null,
                'cargo_id' => $user->cargo_id ?? null,
                'empresa_id' => $user->empresa_id ?? null,
                'empresa_tipo' => $empresaTipo,
            ]);

            abort(403, "Acesso negado. Este painel é exclusivo para {$tipo}. Sua empresa é do tipo: {$empresaTipo}.");
        }

        $tipoPainel = $user->empresa->tipoPainel;
        session(['painel_ativo_id' => $tipoPainel->id]);
        session(['painel_ativo_nome' => $tipoPainel->nome?->value ?? $tipoPainel->nome ?? null]);

        Inertia::share([
            'isEmpresaTipo' => $user->isEmpresaTipo($tipo),
            'empresaTipo' => $user->getTipoEmpresa(),
            'modulo' => $tipoPainel->routePrefix(),
        ]);

=======
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

>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
        return $next($request);
    }
}
