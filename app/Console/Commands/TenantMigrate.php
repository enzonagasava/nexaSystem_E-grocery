<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class TenantMigrate extends Command
{
    protected $signature = 'tenants:migrate {id? : Tenant ID} {--id= : Tenant ID (alternative flag)}';
    protected $description = 'Run migrate on tenant_{id}_content using tenant_content connection template';

    public function handle(): int
    {
        $id = (string) ($this->argument('id') ?? $this->option('id'));
        $this->info("Preparing migrations for tenant: {$id}");

        $template = config('database.connections.tenant_content') ?: config('database.connections.mysql');
        if (! $template) {
            $this->error('No template connection found (tenant_content or mysql).');
            return 1;
        }

        $connName = 'tenant_migration_'.$id;
        $cfg = $template;
        $cfg['database'] = "tenant_{$id}_content";

        config(["database.connections.{$connName}" => $cfg]);

        try {
            $this->info("Running migrate on connection {$connName} (database={$cfg['database']})");
            $path = 'database/migrations/content';
            Artisan::call('migrate', [
                '--database' => $connName,
                '--path' => $path,
                '--force' => true,
            ]);
            $this->info(Artisan::output());
            $this->info('migrate completed.');
            return 0;
        } catch (\Throwable $e) {
            $this->error('Migration failed: '.$e->getMessage());
            return 1;
        }
    }
}
