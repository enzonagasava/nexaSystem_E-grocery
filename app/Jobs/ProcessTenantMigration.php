<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Jobs\NotTenantAware;

class ProcessTenantMigration implements ShouldQueue, NotTenantAware
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries = 3;
    
    public function __construct(
        private int $tenantId,
        private string $type = 'all',
        private bool $seed = false,
        private string $action = 'migrate',
        private int $step = 0,
        private ?string $path = null // Novo parâmetro
    ) {}
    
    public function handle()
    {
        Log::info("Iniciando migrações para tenant {$this->tenantId} - tipo: {$this->type}");
        
        $tenant = Tenant::find($this->tenantId);
        
        if (!$tenant) {
            Log::error("Tenant {$this->tenantId} não encontrado");
            return;
        }
        
        // Se for 'all', processa ambos os bancos
        if ($this->type === 'all') {
            $this->processDatabase($tenant, 'credentials');
            $this->processDatabase($tenant, 'content');
        } else {
            $this->processDatabase($tenant, $this->type);
        }
        
        Log::info("Migrações concluídas para tenant {$this->tenantId}");
    }
    
    private function processDatabase(Tenant $tenant, string $type): void
    {
        $dbName = 'tenant_' . $tenant->id . '_' . $type;
        $connection = 'tenant_' . $type;
        
        // Determinar o caminho das migrations
        $migrationPath = $this->path ?? "database/migrations/{$type}";
        
        Log::info("Migrando {$type} para tenant {$tenant->id} (banco: {$dbName}) - path: {$migrationPath}");
        
        try {
            // 1. Configurar a conexão com o banco correto
            config(["database.connections.{$connection}.database" => $dbName]);
            
            // 2. Purgar a conexão para forçar reconexão
            DB::purge($connection);
            
            // 3. Verificar se o caminho existe (opcional)
            if (!is_dir(base_path($migrationPath)) && $this->path) {
                Log::warning("Caminho personalizado não encontrado: {$migrationPath}");
            }
            
            // 4. Executar ação (migrate ou rollback)
            if ($this->action === 'rollback') {
                $params = [
                    '--database' => $connection,
                    '--path' => $migrationPath,
                    '--force' => true,
                ];

                if ($this->step > 0) {
                    $params['--step'] = $this->step;
                }

                Artisan::call('migrate:rollback', $params);
                Log::info("✅ Rollback ({$this->step}) de {$type} concluído para tenant {$tenant->id}");
            } else {
                Artisan::call('migrate', [
                    '--database' => $connection,
                    '--path' => $migrationPath,
                    '--force' => true,
                ]);

                Log::info("✅ Migração de {$type} concluída para tenant {$tenant->id}");

                // 5. Executar seeders se solicitado (apenas para migrate)
                if ($this->seed) {
                    $this->runSeeders($connection, $type);
                }
            }
            
        } catch (\Exception $e) {
            Log::error("❌ Erro ao migrar {$type} para tenant {$tenant->id}: " . $e->getMessage());
            throw $e; // Para o queue retentar
        }
    }
    
    private function runSeeders(string $connection, string $type): void
    {
        try {
            // Tentar seeder específico para o tipo
            $seederClass = 'Database\\Seeders\\' . ucfirst($type) . 'DatabaseSeeder';
            
            if (class_exists($seederClass)) {
                Artisan::call('db:seed', [
                    '--database' => $connection,
                    '--class' => $seederClass,
                    '--force' => true,
                ]);
                Log::info("Seeder {$seederClass} executado para {$type}");
            } else {
                // Seeder padrão
                Artisan::call('db:seed', [
                    '--database' => $connection,
                    '--force' => true,
                ]);
                Log::info("Seeder padrão executado para {$type}");
            }
        } catch (\Exception $e) {
            Log::warning("Aviso: não foi possível executar seeders para {$type}: " . $e->getMessage());
        }
    }
    
    public function failed(\Throwable $exception)
    {
        Log::error("Job falhou para tenant {$this->tenantId}: " . $exception->getMessage());
    }
}