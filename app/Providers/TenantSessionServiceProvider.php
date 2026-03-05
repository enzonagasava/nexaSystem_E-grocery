<?php
// app/Providers/TenantSessionServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Tenant;

class TenantSessionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('session', function ($app) {
            return new \Illuminate\Session\SessionManager($app);
        });
        
        $this->app->singleton('session.store', function ($app) {
            return $app->make('session')->driver();
        });
    }
    
    public function boot()
    {
        $this->app->booted(function () {
            if (!$this->app->runningInConsole()) {
                $this->configureTenantSession();
            }
        });
    }
    
    protected function configureTenantSession()
    {
        try {
            // PRIMEIRO: configura uma conexão padrão com o banco landlord
            $defaultDb = env('DB_PG_DATABASE', 'nexasystem');
            
            config(['database.connections.tenant_credentials' => [
                'driver' => 'pgsql',
                'host' => env('DB_PG_HOST', 'nexasystem-db'),
                'port' => env('DB_PG_PORT', '5432'),
                'database' => $defaultDb, // USA O BANCO LANDLORD COMO PADRÃO
                'username' => env('DB_PG_USERNAME', 'postgre'),
                'password' => env('DB_PG_PASSWORD', 'root'),
                'charset' => 'utf8',
                'prefix' => '',
                'schema' => 'public',
                'sslmode' => 'prefer',
            ]]);
            
            // SEGUNDO: configura a sessão para usar tenant_credentials
            config(['session.connection' => 'tenant_credentials']);
            
            // TERCEIRO: se houver tenant, tenta usar o banco do tenant
            $tenant = Tenant::current();
            
            if ($tenant) {
                $tenantDb = 'tenant_' . $tenant->id . '_credentials';
                
                // Verifica se o banco do tenant existe
                $exists = DB::connection('tenant_credentials')
                    ->select("SELECT 1 FROM pg_database WHERE datname = ?", [$tenantDb]);
                
                if ($exists) {
                    // Se existir, troca para o banco do tenant
                    config(['database.connections.tenant_credentials.database' => $tenantDb]);
                    Log::info("Usando banco do tenant: {$tenantDb}");
                }
            }
            
            // Purga e reconecta
            DB::purge('tenant_credentials');
            DB::reconnect('tenant_credentials');
            
            Log::info("Sessão configurada com database: " . config('database.connections.tenant_credentials.database'));
            
        } catch (\Exception $e) {
            Log::error("Erro ao configurar sessão: " . $e->getMessage());
            
            // FALLBACK: usa file session
            config(['session.driver' => 'file']);
            config(['session.connection' => null]);
        }
    }
}