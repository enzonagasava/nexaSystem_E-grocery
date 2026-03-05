<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Jobs\ProcessTenantMigration;

class TenantMigrateAllQueue extends Command
{
    protected $signature = 'tenant:migrate-all:queue 
                            {--type=all : Tipo de migração (credentials, content, all)}
                            {--seed : Executar seeders após migração}
                            {--chunk=50 : Quantidade de jobs por lote}
                            {--queue=migrations : Nome da fila}
                            {--delay=0 : Delay entre jobs em segundos}
                            {--only= : IDs específicos separados por vírgula}
                            {--except= : IDs para excluir separados por vírgula}
                            {--rollback : Executar rollback ao invés de migrate}
                            {--step=1 : Quantidade de passos para rollback (apenas para --rollback)}
                            {--path= : Caminho personalizado para as migrations (sobrescreve o padrão)}';
    
    protected $description = 'Enfileira migrações para TODOS os tenants usando queue';
    
    public function handle()
    {
        // Obter tenants baseado nas opções
        $query = Tenant::query();
        
        if ($this->option('only')) {
            $ids = explode(',', $this->option('only'));
            $query->whereIn('id', $ids);
        }
        
        if ($this->option('except')) {
            $ids = explode(',', $this->option('except'));
            $query->whereNotIn('id', $ids);
        }
        
        $tenants = $query->get();
        $total = $tenants->count();
        
        if ($total === 0) {
            $this->error('Nenhum tenant encontrado!');
            return 1;
        }
        
        $isRollback = (bool) $this->option('rollback');
        $step = (int) $this->option('step');

        $this->info(($isRollback ? "Enfileirando rollbacks" : "Enfileirando migrações") . " para {$total} tenants...");
        $this->table(
            ['ID', 'Nome', 'Status'],
            $tenants->map(fn($t) => [$t->id, $t->nome, '⏳ Pendente'])
        );
        
        if (!$this->confirm("Deseja enfileirar {$total} jobs?", true)) {
            $this->info('Operação cancelada');
            return 0;
        }
        
        $type = $this->option('type');
        $seed = $this->option('seed');
        $chunkSize = (int)$this->option('chunk');
        $queueName = $this->option('queue');
        $delay = (int)$this->option('delay');
        $path = $this->option('path'); // Capturar o path personalizado
        
        $bar = $this->output->createProgressBar($total);
        $bar->start();
        
        foreach ($tenants->chunk($chunkSize) as $chunk) {
            foreach ($chunk as $tenant) {
                ProcessTenantMigration::dispatch(
                    $tenant->id, 
                    $type, 
                    $seed, 
                    $isRollback ? 'rollback' : 'migrate', 
                    $step,
                    $path // Passar o path para o job
                )->onQueue($queueName)->delay(now()->addSeconds($delay));
                
                $bar->advance();
            }
        }
        
        $bar->finish();
        
        $this->newLine(2);
        $this->info("✅ {$total} jobs enfileirados na queue '{$queueName}'");
        
        if ($path) {
            $this->line("");
            $this->info("📁 Usando caminho personalizado: {$path}");
        }
        
        $this->line("");
        $this->info("📋 Para processar os jobs, execute:");
        $this->comment("    php artisan queue:work --queue={$queueName} --tries=3 --timeout=600");
        $this->line("");
        $this->info("📊 Para monitorar:");
        $this->comment("    php artisan queue:failed (ver falhas)");
        $this->comment("    php artisan queue:monitor migrations (monitorar fila)");
        
        return 0;
    }
}