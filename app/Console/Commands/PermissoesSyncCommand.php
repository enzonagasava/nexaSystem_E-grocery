<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Services\PermissaoSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PermissoesSyncCommand extends Command
{
    protected $signature = 'permissoes:sync {--tenant= : ID do tenant (opcional; se omitido, processa todos)}';

    protected $description = 'Sincroniza módulos e permissões com config/modulos.php para um ou todos os tenants';

    public function handle(): int
    {
        $tenantId = $this->option('tenant');

        if ($tenantId !== null && $tenantId !== '') {
            $tenantId = (int) $tenantId;
            if ($tenantId < 1) {
                $this->error('O valor de --tenant deve ser um ID válido.');
                return 1;
            }
            return $this->syncTenant($tenantId) ? 0 : 1;
        }

        $tenants = Cliente::on('nexa_admin')->get(['id', 'nome', 'subdominio']);

        if ($tenants->isEmpty()) {
            $this->warn('Nenhum tenant encontrado no banco nexa_admin.');
            return 0;
        }

        $this->info('Sincronizando permissões para ' . $tenants->count() . ' tenant(s)...');

        $failed = 0;
        foreach ($tenants as $tenant) {
            if (!$this->syncTenant($tenant->id)) {
                $failed++;
            }
        }

        if ($failed > 0) {
            $this->warn("Concluído com {$failed} tenant(s) ignorado(s) ou com erro.");
            return 1;
        }

        $this->info('Todos os tenants foram sincronizados.');
        return 0;
    }

    protected function syncTenant(int $tenantId): bool
    {
        try {
            $this->configureTenantConnections($tenantId);
        } catch (\Exception $e) {
            $this->warn("Tenant {$tenantId}: erro ao conectar — {$e->getMessage()}");
            return false;
        }

        if (!Schema::connection('tenant_credentials')->hasTable('users')) {
            $this->warn("Tenant {$tenantId}: tabela 'users' não encontrada. Execute as migrações de credentials primeiro.");
            return false;
        }

        if (!Schema::connection('tenant_credentials')->hasTable('cargos')) {
            $this->warn("Tenant {$tenantId}: tabela 'cargos' não encontrada. Execute as migrações de credentials primeiro.");
            return false;
        }

        try {
            app(PermissaoSyncService::class)->sync();
            $this->line("Tenant {$tenantId}... OK");
            return true;
        } catch (\Exception $e) {
            $this->warn("Tenant {$tenantId}: erro ao sincronizar — {$e->getMessage()}");
            return false;
        }
    }

    protected function configureTenantConnections(int $tenantId): void
    {
        $credentialsDb = "tenant_{$tenantId}_credentials";
        $contentDb = "tenant_{$tenantId}_content";

        config([
            'database.connections.tenant_credentials.database' => $credentialsDb,
            'database.connections.tenant_content.database' => $contentDb,
        ]);

        DB::purge('tenant_credentials');
        DB::purge('tenant_content');

        DB::connection('tenant_credentials')->getPdo();
        DB::connection('tenant_content')->getPdo();
    }
}
