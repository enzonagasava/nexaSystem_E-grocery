<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\Cliente;
use App\Models\Modulo;
use App\Models\Permissao;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RolePermissaoSeeder extends Seeder
{
    private ?int $tenantIdForced = null;

    public function setTenantId(int $tenantId): self
    {
        $this->tenantIdForced = $tenantId;
        return $this;
    }

    /**
     * Cria módulos, permissões, associa módulos aos tipo_painel e cargos às permissões por painel.
     * Executar no contexto do tenant (credentials). Use --tenant=ID ou responda à pergunta interativa.
     */
    public function run(): void
    {
        $tenantId = $this->getTenantId();

        if (!$tenantId) {
            $this->command?->error('❌ Tenant não especificado. Use: php artisan db:seed --class=RolePermissaoSeeder --tenant=13');
            return;
        }

        $this->command?->info("🚀 Iniciando RolePermissaoSeeder para tenant_id: {$tenantId}");
        $this->configureTenantConnections($tenantId);

        if (!Schema::connection('tenant_credentials')->hasTable('users')) {
            $this->command?->error('❌ Tabela "users" não encontrada no tenant. Execute as migrações de credentials primeiro.');
            return;
        }

        if (!Schema::connection('tenant_credentials')->hasTable('cargos')) {
            $this->command?->error('❌ Tabela "cargos" não encontrada no tenant. Execute as migrações de credentials primeiro.');
            return;
        }

        $this->seedModulos();
        $this->seedPermissoes();
        $this->seedPainelModulo();
        $this->seedCargoPermissao();

        $this->command?->info("✨ RolePermissaoSeeder finalizado para tenant {$tenantId}!");
    }

    protected function seedModulos(): void
    {
        $modulos = [
            ['nome' => 'chat', 'display_name' => 'Chat', 'descricao' => 'Módulo de chat'],
            ['nome' => 'agenda', 'display_name' => 'Agenda', 'descricao' => 'Agenda e calendário'],
            ['nome' => 'imoveis', 'display_name' => 'Imóveis', 'descricao' => 'Gestão de imóveis'],
            ['nome' => 'pacientes', 'display_name' => 'Pacientes', 'descricao' => 'Cadastro de pacientes'],
            ['nome' => 'financeiro', 'display_name' => 'Financeiro', 'descricao' => 'Módulo financeiro'],
            ['nome' => 'leads', 'display_name' => 'Leads', 'descricao' => 'Gestão de leads'],
        ];

        foreach ($modulos as $m) {
            Modulo::firstOrCreate(
                ['nome' => $m['nome']],
                ['display_name' => $m['display_name'], 
                'descricao' => $m['descricao'] ?? null]
            );
        }

        $this->command?->info('Módulos criados/verificados.');
    }

    protected function seedPermissoes(): void
    {
        $acoes = [
            ['nome' => 'visualizar', 'display_suffix' => 'Visualizar'],
            ['nome' => 'criar', 'display_suffix' => 'Criar'],
            ['nome' => 'editar', 'display_suffix' => 'Editar'],
            ['nome' => 'excluir', 'display_suffix' => 'Excluir'],
        ];

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

        $this->command?->info('Permissões criadas/verificadas.');
    }

    protected function seedPainelModulo(): void
    {
        $tipos = DB::connection('nexa_admin')->table('tipo_painel')->get();
        $modulos = Modulo::all()->keyBy('nome');

        $regras = [
            'CRM Ecommerce' => ['chat', 'agenda', 'financeiro'],
            'CRM Clínica' => ['chat', 'agenda', 'pacientes', 'financeiro'],
            'CRM Imobiliário' => ['chat', 'agenda', 'imoveis', 'leads', 'financeiro'],
        ];

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

        $this->command?->info('Painel-Módulo associados.');
    }

    protected function seedCargoPermissao(): void
    {
        $tipos = DB::connection('nexa_admin')->table('tipo_painel')->get();
        $permissoes = Permissao::all();
        $adminCargo = Cargo::find(1);

        if (!$adminCargo) {
            $this->command?->warn('Cargo admin (id 1) não encontrado. Pulando cargo_permissao.');
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

        $this->command?->info('Cargo admin com todas as permissões por painel.');
    }

    private function getTenantId(): ?int
    {
        if ($this->tenantIdForced !== null) {
            return $this->tenantIdForced;
        }
        if (function_exists('tenant') && tenant()) {
            return tenant()->id;
        }

        if (isset($_SERVER['argv'])) {
            foreach ($_SERVER['argv'] as $arg) {
                if (strpos($arg, '--tenant=') === 0) {
                    return (int) substr($arg, 9);
                }
            }
        }

        if (php_sapi_name() === 'cli' && defined('STDIN') && $this->command?->getOutput()) {
            $tenants = Cliente::on('nexa_admin')->get(['id', 'nome', 'subdominio']);

            if ($tenants->isEmpty()) {
                $this->command?->error('❌ Nenhum tenant encontrado no banco nexa_admin');
                return null;
            }

            $this->command?->info('📋 Tenants disponíveis:');
            foreach ($tenants as $t) {
                $this->command?->line("   ID: {$t->id} | Nome: {$t->nome} | Domínio: {$t->subdominio}");
            }

            $tenantId = $this->command?->ask('Qual tenant_id você deseja seeder? (digite o ID)');
            return is_numeric($tenantId) ? (int) $tenantId : null;
        }

        return null;
    }

    private function configureTenantConnections(int $tenantId): void
    {
        $credentialsDb = "tenant_{$tenantId}_credentials";
        $contentDb = "tenant_{$tenantId}_content";

        config([
            'database.connections.tenant_credentials.database' => $credentialsDb,
            'database.connections.tenant_content.database' => $contentDb,
        ]);

        DB::purge('tenant_credentials');
        DB::purge('tenant_content');

        try {
            DB::connection('tenant_credentials')->getPdo();
            DB::connection('tenant_content')->getPdo();
            $this->command?->info("✅ Conectado a: {$credentialsDb} e {$contentDb}");
        } catch (\Exception $e) {
            $this->command?->error("❌ Erro ao conectar: {$e->getMessage()}");
            throw $e;
        }
    }
}
