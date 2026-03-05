<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\ImovelSeeder;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

class SeedImovel extends Command
{
    protected $signature = 'seed:imovel {--tenant= : Tenant ID to seed}';

    protected $description = 'Seed imóveis for a specific tenant';

    public function handle()
    {
        $tenantId = $this->getTenantId();

        if (!$tenantId) {
            $this->error('❌ Tenant não especificado.');
            return 1;
        }

        $this->info("🚀 Iniciando SeedImovel para tenant_id: {$tenantId}");

        // Configure tenant connections
        $this->configureTenantConnections($tenantId);

        // Create seeder instance with tenant ID
        $seeder = new ImovelSeeder();
        $seeder->setCommand($this)->setTenantId($tenantId);

        // Call the run method
        $seeder->run();

        return 0;
    }

    private function getTenantId(): ?int
    {
        // First: check for --tenant option
        if ($this->option('tenant')) {
            $tenantId = (int) $this->option('tenant');
            if ($tenantId > 0) {
                return $tenantId;
            }
        }

        // Second: ask user to select from available tenants
        $tenants = Cliente::all(['id', 'nome', 'subdominio']);

        if ($tenants->isEmpty()) {
            $this->error('❌ Nenhum tenant encontrado no banco nexa_admin');
            return null;
        }

        $this->info('📋 Tenants disponíveis:');
        foreach ($tenants as $t) {
            $this->line("   ID: {$t->id} | Nome: {$t->nome} | Domínio: {$t->subdominio}");
        }

        $tenantId = $this->ask('Qual tenant_id você deseja seeder? (digite o ID)');

        if (!is_numeric($tenantId) || $tenantId <= 0) {
            $this->error('❌ ID inválido');
            return null;
        }

        return (int) $tenantId;
    }

    private function configureTenantConnections(int $tenantId): void
    {
        $credentialsDb = "tenant_{$tenantId}_credentials";
        $contentDb = "tenant_{$tenantId}_content";

        config([
            'database.connections.tenant_credentials.database' => $credentialsDb,
            'database.connections.tenant_content.database' => $contentDb,
        ]);

        // Reconnect
        DB::purge('tenant_credentials');
        DB::purge('tenant_content');

        // Verify connections
        try {
            DB::connection('tenant_credentials')->getPdo();
            $this->info("✅ Conectado a: {$credentialsDb}");
        } catch (\Exception $e) {
            $this->error("❌ Erro ao conectar em {$credentialsDb}: {$e->getMessage()}");
            throw $e;
        }

        try {
            DB::connection('tenant_content')->getPdo();
            $this->info("✅ Conectado a: {$contentDb}");
        } catch (\Exception $e) {
            $this->error("❌ Erro ao conectar em {$contentDb}: {$e->getMessage()}");
            throw $e;
        }
    }
}
