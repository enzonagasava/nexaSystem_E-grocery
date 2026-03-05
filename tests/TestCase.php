<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Executar migrações se necessário
        $this->runMigrationsIfNeeded();
    }
    
    protected function runMigrationsIfNeeded(): void
    {
        $connections = ['tenant_credentials', 'tenant_content'];
        
        foreach ($connections as $connection) {
            // Verificar se a tabela 'migrations' existe
            $migrationsTableExists = false;
            
            try {
                $migrationsTableExists = \DB::connection($connection)
                    ->getSchemaBuilder()
                    ->hasTable('migrations');
            } catch (\Exception $e) {
                // Tabela não existe
            }
            
            // Se não tem tabela migrations, executar migrações
            if (!$migrationsTableExists) {
                Artisan::call('migrate', [
                    '--database' => $connection,
                    '--force' => true,
                ]);
            }
        }
    }
}