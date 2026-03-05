<?php

namespace App\Http\Controllers\Admin\Corretor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Imovel;
use App\Models\User;
use App\Models\Listing;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $empresaId = $user->empresa_id;

        $periodoValor = (int) $request->input('periodoValor', 30);
        $periodoComissoes = (int) $request->input('periodoComissoes', 30);
        $periodoImoveis = (int) $request->input('periodoImoveis', 30);
        $periodoChats = (int) $request->input('periodoChats', 30);

        // Buscar IDs dos usuários da empresa
        $userIds = User::where('empresa_id', $empresaId)->pluck('id')->toArray();

        if (empty($userIds)) {
            return Inertia::render('admin/corretor/Dashboard', [
                'dashboardData' => [
                    'valorVendido' => [],
                    'imoveisVendidos' => [],
                    'anunciosTipos' => [],
                    'comissoesPorImovel' => [],
                    'novosChatsPorMes' => [],
                ],
                'kpiData' => [
                    'vendasTotais' => 0,
                    'imoveisAtivos' => 0,
                    'visualizacoes' => 0,
                    'vendasFechadas' => 0,
                ],
                'periodoValor' => (string) $periodoValor,
                'periodoComissoes' => (string) $periodoComissoes,
                'periodoImoveis' => (string) $periodoImoveis,
                'periodoChats' => (string) $periodoChats,
                'modulo' => 'corretor',
            ]);
        }

        // 1. Valor Vendido - Soma dos valores de venda dos imóveis vendidos ao longo do tempo
        $valorVendido = Imovel::getValorVendidoPeriodo($userIds, $periodoValor);

        // 2. Imóveis Vendidos - Distribuição por categoria dos imóveis vendidos
        $imoveisVendidos = Imovel::getImoveisVendidosPorCategoria($userIds, $periodoImoveis);

        // 2b. Tipos de Anúncios - Distribuição de anúncios por tipo/plataforma
        $anunciosTipos = $this->getAnunciosTiposPorUsuarios($userIds);

        // 3. Comissões por Dia - soma das comissões por date (últimos $periodoComissoes dias)
        $comissoesPorImovel = Imovel::getComissoesPeriodo($userIds, $periodoComissoes);

        // 4. Novos Chats por Dia (mocked) - últimos 7 dias
        $novosChatsPorMes = collect();

        // valores mockados (do dia mais antigo ao mais recente)
        $mockCounts = [5, 8, 3, 7, 6, 4, 9];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $index = 6 - $i;
            $novosChatsPorMes->push([
                'mes' => $date->format('d/m'),
                'total' => (int) ($mockCounts[$index] ?? 0),
            ]);
        }

        // KPI Cards - Métricas principais
        $vendasTotais = Imovel::getVendasTotais($userIds);
        $imoveisAtivos = Imovel::getImoveisAtivos($userIds);
        $vendasFechadas = Imovel::getVendasFechadas($userIds);
        $faturamento = Imovel::getFaturamentoPeriodo($userIds, $periodoValor);
        $locacao = Imovel::getLocacaoPeriodo($userIds, $periodoValor);
        $locacoesCount = Imovel::getLocacoesCountPeriodo($userIds, $periodoValor);

        // Visualizações - Mockado por enquanto
        $visualizacoes = 1245;

        return Inertia::render('admin/corretor/Dashboard', [
            'dashboardData' => [
                'valorVendido' => $valorVendido,
                'imoveisVendidos' => $imoveisVendidos,
                'anunciosTipos' => $anunciosTipos,
                'comissoesPorImovel' => $comissoesPorImovel,
                'novosChatsPorMes' => $novosChatsPorMes,
                'faturamento' => $faturamento,
                'locacao' => $locacao,
                'locacoesCount' => $locacoesCount,
            ],
            'kpiData' => [
                'vendasTotais' => (float) $vendasTotais,
                'imoveisAtivos' => (int) $imoveisAtivos,
                'visualizacoes' => (int) $visualizacoes,
                'vendasFechadas' => (int) $vendasFechadas,
            ],
            'periodoValor' => (string) $periodoValor,
            'periodoComissoes' => (string) $periodoComissoes,
            'periodoImoveis' => (string) $periodoImoveis,
            'periodoChats' => (string) $periodoChats,
            'modulo' => 'corretor',
        ]);
    }

    /**
     * Buscar tipos de anúncios e contar por tipo para os usuários da empresa
     */
    private function getAnunciosTiposPorUsuarios(array $userIds): array
    {
        $listings = Listing::with('imovel')
            ->whereHas('imovel', fn($query) => $query->whereIn('user_id', $userIds))
            ->where('anuncio_ativo', true)
            ->get();

        // Agrupar tipos de anúncio com contagem
        $tiposCount = collect();

        foreach ($listings as $listing) {
            $tipos = $listing->anuncio_tipos ?? [];
            if (is_array($tipos)) {
                foreach ($tipos as $tipo) {
                    $tiposCount[$tipo] = ($tiposCount[$tipo] ?? 0) + 1;
                }
            }
        }

        // Converter para array de objetos
        return $tiposCount->map(fn($quantidade, $tipo) => [
            'tipo' => $tipo,
            'quantidade' => $quantidade,
        ])->values()->toArray();
    }
}
