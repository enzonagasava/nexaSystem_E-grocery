<?php
// database/seeders/StatusSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Cliente;
use Illuminate\Support\Facades\Schema;


class StatusSeeder extends Seeder
{
    private ?int $tenantIdForced = null;

    public function setTenantId(int $tenantId): self
    {
        $this->tenantIdForced = $tenantId;
        return $this;
    }

    public function run(): void
    {
        $tenantId = $this->getTenantId();
        
        if (!$tenantId) {
            $this->command->error('❌ Tenant não especificado.');
            $this->command->error('Use: php artisan db:seed --class=StatusSeeder --tenant=13');
            $this->command->error('Ou execute em um contexto de tenant (via job)');
            return;
        }

        $this->command->info("🚀 Iniciando StatusSeeder para tenant_id: {$tenantId}");
        $this->configureTenantConnections($tenantId);

        // Verificar se a tabela existe
        if (!Schema::connection('tenant_content')->hasTable('status')) {
            $this->command->error('❌ Tabela "status" não existe!');
            return;
        }

        // Limpar status existentes (opcional)
        // DB::connection('tenant_content')->table('status')->truncate();

        $status = [
            // Status para Leads
            ['nome' => 'Novo Lead', 'descricao' => 'Lead que acabou de entrar no sistema', 'ordem' => 10],
            ['nome' => 'Em Simulação', 'descricao' => 'Lead em processo de simulação', 'ordem' => 20],
            ['nome' => 'Agendou Visita', 'descricao' => 'Lead que agendou visita', 'ordem' => 30],
            ['nome' => 'Em Negociação', 'descricao' => 'Lead em negociação', 'ordem' => 40],
            ['nome' => 'Convertido', 'descricao' => 'Lead convertido para contato', 'ordem' => 50],
            ['nome' => 'Perdido', 'descricao' => 'Lead perdido', 'ordem' => 60],
            
            // Status para Contatos
            ['nome' => 'Documentação', 'descricao' => 'Contato reunindo documentação', 'ordem' => 10],
            ['nome' => 'Aprovação Financiamento', 'descricao' => 'Contato aguardando aprovação', 'ordem' => 20],
            ['nome' => 'Fechamento', 'descricao' => 'Contato na fase final', 'ordem' => 30],
            ['nome' => 'Concluído', 'descricao' => 'Processo finalizado', 'ordem' => 40],
        ];

        foreach ($status as $item) {
            DB::connection('tenant_content')
                ->table('status')
                ->insert(array_merge($item, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
        }

        $count = DB::connection('tenant_content')->table('status')->count();
        $this->command->info("✅ StatusSeeder concluído! {$count} registros inseridos para tenant {$tenantId}.");
    }

    private function getTenantId(): ?int
    {
        // 1. Se foi forçado via setTenantId()
        if ($this->tenantIdForced !== null) {
            return $this->tenantIdForced;
        }

        // 2. Se está rodando em um tenant (via job/middleware)
        if (function_exists('tenant') && tenant()) {
            return tenant()->id;
        }

        // 3. Se foi passado como argumento de linha de comando
        if (isset($_SERVER['argv'])) {
            foreach ($_SERVER['argv'] as $arg) {
                if (strpos($arg, '--tenant=') === 0) {
                    return (int) substr($arg, 9);
                }
            }
        }

    if (php_sapi_name() === 'cli' && defined('STDIN')) {
        // Verifica se o método isInteractive existe antes de chamar
        $output = $this->command->getOutput();
        $isInteractive = method_exists($output, 'isInteractive') ? $output->isInteractive() : true;
        
        if ($isInteractive) {
            return $this->askForTenantInteractively();
        }
    }

        // 5. Se chegou aqui, não tem como determinar o tenant
        return null;
    }

    private function askForTenantInteractively(): ?int
    {
        $tenants = Cliente::on('nexa_admin')->get(['id', 'nome', 'subdominio']);
        
        if ($tenants->isEmpty()) {
            $this->command->error('❌ Nenhum tenant encontrado no banco nexa_admin');
            return null;
        }

        $this->command->info('📋 Tenants disponíveis:');
        foreach ($tenants as $t) {
            $this->command->line("   ID: {$t->id} | Nome: {$t->nome} | Domínio: {$t->subdominio}");
        }

        $tenantId = $this->command->ask('Qual tenant_id você deseja seeder? (digite o ID)');
        return is_numeric($tenantId) ? (int) $tenantId : null;
    }

    private function configureTenantConnections(int $tenantId): void
    {
        config(['database.connections.tenant_content.database' => "tenant_{$tenantId}_content"]);
        DB::purge('tenant_content');
        DB::connection('tenant_content')->getPdo();
        $this->command->info("✅ Conectado a: tenant_{$tenantId}_content");
    }
}