<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConfigureTenantDatabase
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        $mainSubdomain = env('MAIN_SUBDOMAIN', 'localhost');
        
        Log::info("🌐 ConfigureTenantDatabase - Host: {$host}, Main: {$mainSubdomain}");
        
        // Se for o domínio principal, não configura tenant
        if ($host === $mainSubdomain) {
            Log::info("✅ ConfigureTenantDatabase - Domínio principal, pulando configuração de tenant");
            return $next($request);
        }

        // Usa o TenantFinder configurado no Spatie
        $tenantFinder = app(config('multitenancy.tenant_finder'));
        $tenant = $tenantFinder->findForRequest($request);

        if ($tenant) {
            Log::info("✅ ConfigureTenantDatabase - Tenant encontrado: ID={$tenant->id}");
            
            // O makeCurrent() já chama o SwitchMultiDatabaseTask configurado
            // que configura os bancos de dados automaticamente
            $tenant->makeCurrent();
            
            // Força purge e reconexão para garantir
            $this->ensureDatabaseConnections($tenant);
            
            // Configura a sessão após o tenant estar definido
            $this->configureSession($tenant);
            
        } else {
            Log::warning("⚠️ ConfigureTenantDatabase - Nenhum tenant encontrado para host: {$host}");
            // Não usa fallback - deixa sem tenant para rotas públicas funcionarem
        }

        return $next($request);
    }
    
    protected function ensureDatabaseConnections(Tenant $tenant): void
    {
        $credentialsDb = 'tenant_' . $tenant->id . '_credentials';
        $contentDb = 'tenant_' . $tenant->id . '_content';
        
        Log::info("🔧 ConfigureTenantDatabase - Configurando bancos: {$credentialsDb}, {$contentDb}");
        
        // PURGE crítico - força nova conexão
        DB::purge('tenant_credentials');
        DB::purge('tenant_content');
        
        // Reconecta imediatamente
        DB::reconnect('tenant_credentials');
        DB::reconnect('tenant_content');
        
        // Testa conexão
        try {
            DB::connection('tenant_credentials')->getPdo();
            Log::info("✅ ConfigureTenantDatabase - Conexão tenant_credentials OK");
        } catch (\Exception $e) {
            Log::error("❌ ConfigureTenantDatabase - Erro conexão credentials: " . $e->getMessage());
        }
        
        try {
            DB::connection('tenant_content')->getPdo();
            Log::info("✅ ConfigureTenantDatabase - Conexão tenant_content OK");
        } catch (\Exception $e) {
            Log::error("❌ ConfigureTenantDatabase - Erro conexão content: " . $e->getMessage());
        }
    }
    
    protected function configureSession(Tenant $tenant): void
    {
        // Usa sessão em arquivo por padrão para evitar problemas de bootstrap
        $sessionDriver = env('SESSION_DRIVER', 'file');
        
        if ($sessionDriver === 'database') {
            // Se usar sessão em banco, configura para usar tenant_credentials
            config(['session.connection' => 'tenant_credentials']);
            
            // Verifica se o banco está configurado
            $dbName = config('database.connections.tenant_credentials.database');
            
            if (empty($dbName)) {
                Log::error('❌ ConfigureTenantDatabase - Banco não configurado para sessão!');
                // Fallback para file
                config(['session.driver' => 'file']);
                config(['session.connection' => null]);
                return;
            }
            
            Log::info("✅ ConfigureTenantDatabase - Sessão configurada com database: {$dbName}");
            
            // Força recriação do session handler
            $this->rebuildSessionHandler();
        } else {
            Log::info("✅ ConfigureTenantDatabase - Sessão usando driver: {$sessionDriver}");
        }
    }
    
    protected function rebuildSessionHandler(): void
    {
        try {
            // Remove instâncias antigas
            app()->forgetInstance('session.store');
            app()->forgetInstance('session');
            
            // Recria
            app()->make('session');
            
            Log::info("✅ ConfigureTenantDatabase - Session handler recriado");
            
        } catch (\Exception $e) {
            Log::error("❌ ConfigureTenantDatabase - Erro ao recriar session handler: " . $e->getMessage());
        }
    }
}