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
<<<<<<< HEAD
        $connections = ['tenant_credentials', 'tenant_content'];
=======
        $connections = ['credentials', 'content'];
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
        
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