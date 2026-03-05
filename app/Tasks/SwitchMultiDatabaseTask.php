<?php

namespace App\Tasks;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;
use Spatie\Multitenancy\Tasks\SwitchTenantDatabaseTask;

class SwitchMultiDatabaseTask extends SwitchTenantDatabaseTask
{
    public function makeCurrent($tenant): void
    {
        Log::info("🔄 SwitchMultiDatabaseTask - Iniciando troca para tenant: ID={$tenant->id}");
        
        $contentConnection = config('multitenancy.tenant_database_connection_name');
        $contentDb = 'tenant_' . $tenant->id . '_content';
        $credentialsDb = 'tenant_' . $tenant->id . '_credentials';
        
        // Define o banco de dados para tenant_content (padrão)
        $this->setTenantConnectionDatabase('tenant_content', $contentDb);
        Log::info("✅ SwitchMultiDatabaseTask - Configurado {$contentConnection}: {$contentDb}");
        
        // Define o banco de dados para tenant_credentials
        $this->setTenantConnectionDatabase('tenant_credentials', $credentialsDb);
        Log::info("✅ SwitchMultiDatabaseTask - Configurado tenant_credentials: {$credentialsDb}");
        
        // Purge para forçar reconexão
        DB::purge($contentConnection);
        DB::purge('tenant_credentials');

        
        Log::info("🔄 SwitchMultiDatabaseTask - Conexões purgadas e prontas para reconexão");
    }

    private function setTenantConnectionDatabase(string $connectionName, string $databaseName): void
    {
        config([
            "database.connections.{$connectionName}.database" => $databaseName,
        ]);
    }
    
    public function forgetCurrent(): void
    {
        Log::info("🔄 SwitchMultiDatabaseTask - Restaurando para landlord");
        
        // Restaura para conexão landlord
        config(['database.default' => 'nexa_admin']);
        DB::purge();
        
        Log::info("✅ SwitchMultiDatabaseTask - Restaurado para nexa_admin");
    }
}