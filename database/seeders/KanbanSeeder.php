<?php
// database/seeders/KanbanSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Cliente;
use Illuminate\Support\Facades\Schema;

class KanbanSeeder extends Seeder
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
            $this->command->error('❌ Tenant não especificado. Use: php artisan db:seed --class=KanbanSeeder --tenant=13');
            return;
        }

        $this->command->info("🚀 Iniciando KanbanSeeder para tenant_id: {$tenantId}");
        $this->configureTenantConnections($tenantId);

        // Verificar se a tabela users existe
        if (!Schema::connection('tenant_credentials')->hasTable('users')) {
            $this->command->error('❌ Tabela "users" não encontrada!');
            $this->command->error('Execute as migrações de credentials primeiro.');
            return;
        }

        // Buscar usuário
        $user = DB::connection('tenant_credentials')->table('users')->first();
        if (!$user) {
            $this->command->error('❌ Nenhum usuário encontrado!');
            $this->command->error('Execute o UserSeeder primeiro.');
            return;
        }

        $this->command->info("✅ Usuário encontrado: ID {$user->id}");

        // Verificar se a tabela status existe
        if (!Schema::connection('tenant_content')->hasTable('status')) {
            $this->command->error('❌ Tabela "status" não encontrada! Execute o StatusSeeder primeiro.');
            return;
        }

        // Verificar se a tabela kanban_quadros existe
        if (!Schema::connection('tenant_content')->hasTable('kanban_quadros')) {
            $this->command->error('❌ Tabela "kanban_quadros" não encontrada!');
            return;
        }

        // Verificar se a tabela kanban_colunas existe
        if (!Schema::connection('tenant_content')->hasTable('kanban_colunas')) {
            $this->command->error('❌ Tabela "kanban_colunas" não encontrada!');
            return;
        }

        // Verificar se já existem quadros
        $quadrosExistentes = DB::connection('tenant_content')->table('kanban_quadros')->count();
        if ($quadrosExistentes > 0) {
            $this->command->warn('⚠️ Quadros já existentes. Pulando criação...');
            
            // Mostrar quadros existentes
            $quadros = DB::connection('tenant_content')->table('kanban_quadros')->get();
            foreach ($quadros as $quadro) {
                $colunas = DB::connection('tenant_content')
                    ->table('kanban_colunas')
                    ->where('kanban_quadro_id', $quadro->id)
                    ->count();
                $this->command->line("   📊 {$quadro->nome}: {$colunas} colunas");
            }
            return;
        }

        // =========================================
        // BUSCAR STATUS
        // =========================================
        $statusLeads = DB::connection('tenant_content')
            ->table('status')
            ->whereIn('nome', ['Novo Lead', 'Em Simulação', 'Agendou Visita', 'Em Negociação', 'Convertido', 'Perdido'])
            ->orderBy('ordem')
            ->get()
            ->keyBy('nome');

        $statusContatos = DB::connection('tenant_content')
            ->table('status')
            ->whereIn('nome', ['Documentação', 'Aprovação Financiamento', 'Fechamento', 'Concluído'])
            ->orderBy('ordem')
            ->get()
            ->keyBy('nome');

        if ($statusLeads->isEmpty()) {
            $this->command->error('❌ Status para leads não encontrados! Execute o StatusSeeder primeiro.');
            return;
        }

        if ($statusContatos->isEmpty()) {
            $this->command->error('❌ Status para contatos não encontrados! Execute o StatusSeeder primeiro.');
            return;
        }

        // =========================================
        // QUADRO 1: LEADS
        // =========================================
        $this->command->info('📦 Criando quadro de LEADS...');

        $quadroLeadsId = DB::connection('tenant_content')->table('kanban_quadros')->insertGetId([
            'user_id' => $user->id,
            'permissao_users' => json_encode([$user->id]),
            'nome' => 'Funil de Leads',
            'descricao' => 'Acompanhamento de leads no funil de vendas',
            'tipo' => 'leads',
            'favoritos' => json_encode([]),
            'is_active' => true,
            'ordem' => 1,
            'padrao' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Configurações visuais para cada status
        $configLeads = [
            'Novo Lead' => ['cor' => 'blue', 'cor_fundo' => '#EFF6FF', 'icone' => 'UserPlus'],
            'Em Simulação' => ['cor' => 'yellow', 'cor_fundo' => '#FEF3C7', 'icone' => 'Calculator'],
            'Agendou Visita' => ['cor' => 'purple', 'cor_fundo' => '#F3E8FF', 'icone' => 'Home'],
            'Em Negociação' => ['cor' => 'green', 'cor_fundo' => '#D1FAE5', 'icone' => 'MessageCircle'],
            'Convertido' => ['cor' => 'gray', 'cor_fundo' => '#F3F4F6', 'icone' => 'CheckCircle'],
            'Perdido' => ['cor' => 'red', 'cor_fundo' => '#FEE2E2', 'icone' => 'XCircle'],
        ];

        foreach ($configLeads as $nomeStatus => $config) {
            $status = $statusLeads->get($nomeStatus);
            
            DB::connection('tenant_content')->table('kanban_colunas')->insert([
                'kanban_quadro_id' => $quadroLeadsId,
                'status_id' => $status->id,  // FK para status
                'descricao' => $status->descricao,
                'cor' => $config['cor'],
                'cor_fundo' => $config['cor_fundo'],
                'icone' => $config['icone'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("  ✅ Quadro de LEADS criado com " . count($configLeads) . " colunas");

        // =========================================
        // QUADRO 2: CONTATOS
        // =========================================
        $this->command->info('📦 Criando quadro de CONTATOS...');

        $quadroContatosId = DB::connection('tenant_content')->table('kanban_quadros')->insertGetId([
            'user_id' => $user->id,
            'permissao_users' => json_encode([$user->id]),
            'nome' => 'Processo de Contratos',
            'descricao' => 'Acompanhamento de contatos em processo',
            'tipo' => 'contatos',
            'favoritos' => json_encode([]),
            'is_active' => true,
            'ordem' => 2,
            'padrao' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Configurações visuais para cada status de contato
        $configContatos = [
            'Documentação' => ['cor' => 'orange', 'cor_fundo' => '#FFEDD5', 'icone' => 'FileText'],
            'Aprovação Financiamento' => ['cor' => 'blue', 'cor_fundo' => '#EFF6FF', 'icone' => 'Banknote'],
            'Fechamento' => ['cor' => 'green', 'cor_fundo' => '#D1FAE5', 'icone' => 'Handshake'],
            'Concluído' => ['cor' => 'gray', 'cor_fundo' => '#F3F4F6', 'icone' => 'Award'],
        ];

        foreach ($configContatos as $nomeStatus => $config) {
            $status = $statusContatos->get($nomeStatus);
            
            DB::connection('tenant_content')->table('kanban_colunas')->insert([
                'kanban_quadro_id' => $quadroContatosId,
                'status_id' => $status->id,  // FK para status
                'descricao' => $status->descricao,
                'cor' => $config['cor'],
                'cor_fundo' => $config['cor_fundo'],
                'icone' => $config['icone'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("  ✅ Quadro de CONTATOS criado com " . count($configContatos) . " colunas");

        // Mostrar resultado
        $totalQuadros = DB::connection('tenant_content')->table('kanban_quadros')->count();
        $totalColunas = DB::connection('tenant_content')->table('kanban_colunas')->count();
        
        $this->command->info('====================================');
        $this->command->info("✨ KanbanSeeder finalizado para tenant {$tenantId}!");
        $this->command->info("   Total de quadros: {$totalQuadros}");
        $this->command->info("   Total de colunas: {$totalColunas}");
    }

    private function getTenantId(): ?int
    {
        if ($this->tenantIdForced !== null) return $this->tenantIdForced;
        if (function_exists('tenant') && tenant()) return tenant()->id;

        // Verificar argumento de linha de comando
        if (isset($_SERVER['argv'])) {
            foreach ($_SERVER['argv'] as $arg) {
                if (strpos($arg, '--tenant=') === 0) {
                    return (int) substr($arg, 9);
                }
            }
        }

        // Se for ambiente interativo, perguntar
        if (php_sapi_name() === 'cli' && defined('STDIN') && $this->command->getOutput()) {
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

        return null;
    }

    private function configureTenantConnections(int $tenantId): void
    {
        $credentialsDb = "tenant_{$tenantId}_credentials";
        $contentDb = "tenant_{$tenantId}_content";

        config([
            'database.connections.tenant_credentials.database' => $credentialsDb,
            'database.connections.tenant_content.database' => $contentDb,
        ]);

        DB::purge('tenant_credentials');
        DB::purge('tenant_content');

        try {
            DB::connection('tenant_credentials')->getPdo();
            DB::connection('tenant_content')->getPdo();
            $this->command->info("✅ Conectado a: {$credentialsDb} e {$contentDb}");
        } catch (\Exception $e) {
            $this->command->error("❌ Erro ao conectar: {$e->getMessage()}");
            throw $e;
        }
    }
}