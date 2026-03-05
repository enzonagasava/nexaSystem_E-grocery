<?php

namespace App\Models;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    protected $table = 'clientes';

    
    public function getNameAttribute()
    {
        return $this->nome;
    }
    
    public function setNameAttribute($value)
    {
        $this->attributes['nome'] = $value;
    }
    

    protected $fillable = [
        'nome',
        'email',
        'numero',
        'database',
        'subdominio',
        'tipo_painel_id',
        'password',
    ];

    protected static function booted()
    {
        static::created(function ($tenant) {
            $tenant->createTenantDatabases();
        });
    }

    public function createTenantDatabases(): void
    {
        $credentialsDb = 'tenant_' . $this->id . '_credentials';
        $contentDb = 'tenant_' . $this->id . '_content';
        
           DB::connection('tenant_credentials')->statement("
                CREATE DATABASE {$credentialsDb} 
                WITH 
                OWNER = 'postgres'
                ENCODING = 'UTF8'
                LC_COLLATE = 'C'
                LC_CTYPE = 'C'
                TEMPLATE = template0;
            ");

            DB::connection('tenant_content')->statement("
                CREATE DATABASE {$contentDb} 
                WITH 
                OWNER = 'postgres'
                ENCODING = 'UTF8'
                LC_COLLATE = 'C'
                LC_CTYPE = 'C'
                TEMPLATE = template0;
            ");
    
        
        Log::info("Bancos criados para tenant {$this->id}: {$credentialsDb}, {$contentDb}");

        $this->runMigrationsAndSeeders();
    }
    
    public function runMigrationsAndSeeders(bool $seed = true): void
    {
        Log::info("Executando migrações para tenant {$this->id}");
        
        // Configura manualmente cada banco
        $this->configureAndMigrate('credentials', $seed);
        $this->configureAndMigrate('content', $seed);
    }

    private function configureAndMigrate(string $type, bool $seed = true): void
    {
        $dbName = 'tenant_' . $this->id . '_' . $type;
        $connection = 'tenant_' . $type;
        $path = database_path("migrations/{$type}");
        
        if (!is_dir($path)) {
            Log::warning("Diretório não existe: {$path}");
            return;
        }
        
        // 1. Configura o banco na conexão
        config(["database.connections.{$connection}.database" => $dbName]);
        
        // 2. Define como padrão temporariamente
        $originalDefault = config('database.default');
        config(['database.default' => $connection]);
        
        // 3. Purge
        DB::purge();
        
        try {
            // 4. Migra
            Artisan::call('migrate', [
                '--database' => $connection,
                '--path' => "database/migrations/{$type}",
                '--force' => true,
            ]);
            
            Log::info("Migrações de {$type} concluídas");
            
            // 5. Executar seeders se solicitado
            if ($seed) {
                $this->runSeeders($type, $connection);
            }
            
        } finally {
            // 6. Restaura
            config(['database.default' => $originalDefault]);
            DB::purge();
        }
    }
    
    private function runSeeders(string $type, string $connection): void
    {
        try {
            // Método 1: Seeder específico por tipo
            $seederClass = 'Database\\Seeders\\' . ucfirst($type) . 'DatabaseSeeder';
            Log::info("Executando seeders para {$seederClass}");
            
            if (class_exists($seederClass)) {
                Artisan::call('db:seed', [
                    '--database' => $connection,
                    '--class' => $seederClass,
                    '--force' => true,
                ]);
                Log::info("Seeder {$seederClass} executado para {$connection}");
            }
            
            // Método 2: Seeder padrão do tipo
            else {
                Artisan::call('db:seed', [
                    '--database' => $connection,
                    '--force' => true,
                ]);
                Log::info("Seeder padrão executado para {$type}");
            }
            
        } catch (\Exception $e) {
            Log::error("Erro ao executar seeder para {$type}: " . $e->getMessage());
        }
    }

    public function getFullDomainAttribute()
{
    // Para desenvolvimento
    if (app()->environment('local')) {
        return $this->subdominio . '.localhost';
    }
    
    // Para produção
    return $this->subdominio . '.seudominio.com';
}
}