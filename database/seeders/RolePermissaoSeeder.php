<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Services\PermissaoSyncService;
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
     * Usa config/modulos.php como fonte. Executar no contexto do tenant (credentials).
     * Use --tenant=ID ou responda à pergunta interativa.
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

        app(PermissaoSyncService::class)->sync();

        $this->command?->info('Módulos e permissões sincronizados.');
        $this->command?->info("✨ RolePermissaoSeeder finalizado para tenant {$tenantId}!");
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
