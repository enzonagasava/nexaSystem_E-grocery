<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class TenantMigrateFresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:migrate:fresh {id? : Tenant ID} {--id= : Tenant ID (alternative flag)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrate:fresh on tenant_{id}_content using tenant_content connection template';

    public function handle(): int
    {
        $id = (string) ($this->argument('id') ?? $this->option('id'));
        $this->info("Preparing migrations fresh for tenant: {$id}");

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
            $this->info("Running migrate:fresh on connection {$connName} (database={$cfg['database']})");
            $path = 'database/migrations/content';
            Artisan::call('migrate:fresh', [
                '--database' => $connName,
                '--path' => $path,
                '--force' => true,
            ]);
            $this->info(Artisan::output());
            $this->info('migrate:fresh completed.');
            return 0;
        } catch (\Throwable $e) {
            $this->error('Migration failed: '.$e->getMessage());
            return 1;
        }
    }
}
