<?php

namespace App\Services;

use App\Models\Cargo;
use App\Models\Modulo;
use App\Models\Permissao;
use Illuminate\Support\Facades\DB;

class PermissaoSyncService
{
    /**
     * Sincroniza módulos, permissões, painel_modulo e cargo_permissao (admin)
     * com base em config('modulos'). A conexão tenant_credentials deve estar
     * já apontando para o banco do tenant; nexa_admin deve estar disponível
     * para ler tipo_painel.
     */
    public function sync(): void
    {
        $this->syncModulos();
        $this->syncPermissoes();
        $this->syncPainelModulo();
        $this->syncCargoPermissao();
    }

    protected function syncModulos(): void
    {
        $modulos = config('modulos.modulos', []);

        foreach ($modulos as $m) {
            Modulo::firstOrCreate(
                ['nome' => $m['nome']],
                [
                    'display_name' => $m['display_name'],
                    'descricao' => $m['descricao'] ?? null,
                ]
            );
        }
    }

    protected function syncPermissoes(): void
    {
        $acoes = config('modulos.acoes', []);

        foreach (Modulo::all() as $modulo) {
            foreach ($acoes as $acao) {
                Permissao::firstOrCreate(
                    [
                        'nome' => $acao['nome'],
                        'recurso' => $modulo->nome,
                        'modulo_id' => $modulo->id,
                    ],
                    ['display_name' => $acao['display_suffix'] . ' ' . $modulo->display_name]
                );
            }
        }
    }

    protected function syncPainelModulo(): void
    {
        $regras = config('modulos.painel_modulos', []);
        $tipos = DB::connection('nexa_admin')->table('tipo_painel')->get();
        $modulos = Modulo::all()->keyBy('nome');

        foreach ($tipos as $tipo) {
            $nomesModulos = $regras[$tipo->nome] ?? ['chat'];
            foreach ($nomesModulos as $nomeModulo) {
                $modulo = $modulos->get($nomeModulo);
                if (!$modulo) {
                    continue;
                }
                DB::connection('tenant_credentials')->table('painel_modulo')->insertOrIgnore([
                    'painel_id' => $tipo->id,
                    'modulo_id' => $modulo->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    protected function syncCargoPermissao(): void
    {
        $tipos = DB::connection('nexa_admin')->table('tipo_painel')->get();
        $permissoes = Permissao::all();
        $adminCargo = Cargo::find(1);

        if (!$adminCargo) {
            return;
        }

        foreach ($tipos as $tipo) {
            foreach ($permissoes as $permissao) {
                DB::connection('tenant_credentials')->table('cargo_permissao')->insertOrIgnore([
                    'cargo_id' => $adminCargo->id,
                    'permissao_id' => $permissao->id,
                    'painel_id' => $tipo->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
