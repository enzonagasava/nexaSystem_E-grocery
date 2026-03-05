<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\Imovel;
use App\Models\ImovelImagem;
use App\Models\ImovelPlanta;
use App\Models\ImovelMidia;
use App\Models\ImovelAutorizacao;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Cliente;
use Carbon\Carbon;

class ImovelSeeder extends Seeder
{
    private ?int $tenantIdForced = null;

    public function setTenantId(int $tenantId): self
    {
        $this->tenantIdForced = $tenantId;
        return $this;
    }

    public function run()
    {
        // Obter tenant_id do argumento ou variável global
        $tenantId = $this->getTenantId();
        
        if (!$tenantId) {
            $this->command->error('❌ Tenant não especificado. Use: php artisan db:seed --seeder=ImovelSeeder --tenant=13');
            return;
        }

        $this->command->info("🚀 Iniciando ImovelSeeder para tenant_id: {$tenantId}");

        // Configurar conexões do tenant
        $this->configureTenantConnections($tenantId);

        $seedImagesPath = resource_path('seed/images');

        $files = [];
        if (File::isDirectory($seedImagesPath)) {
            $files = File::files($seedImagesPath);
        }

        if (empty($files)) {
            $this->command->info('⚠️ Nenhuma imagem encontrada em resources/seed/images. Adicione imagens e rode novamente.');
            return;
        }

        // Garantir diretório público em storage/app/public/imoveis
        $publicImoveisPath = storage_path('app/public/imoveis');
        if (! File::isDirectory($publicImoveisPath)) {
            File::ensureDirectoryExists($publicImoveisPath, 0755, true);
        }

        // Garantir symlink public/storage
        if (! File::isDirectory(public_path('storage'))) {
            try {
                \Illuminate\Support\Facades\Artisan::call('storage:link');
            } catch (\Throwable $e) {
                $this->command->warn('⚠️ Não foi possível criar symlink storage');
            }
        }

        // Usar usuário corretor com id = 1
        $desiredUserId = 1;
        $user = User::on('tenant_credentials')->find($desiredUserId);
        
        if (! $user) {
            $this->command->info('📝 Criando usuário corretor (id=1) na tenant_credentials...');
            User::on('tenant_credentials')->create([
                'id' => $desiredUserId,
                'name' => 'Corretor Seeder',
                'email' => 'corretor+1@example.com',
                'numero' => null,
                'password' => bcrypt('password'),
                'cargo_id' => 2, // Cargo de corretor
                'empresa_id' => 3, // Empresa de corretor
            ]);
            $user = User::on('tenant_credentials')->find($desiredUserId);
            $this->command->info('✅ Usuário criado com sucesso');
        }

        $this->command->info('📦 Criando 20 imóveis com imagens e tipos variados...');

        $categorias = [
            'apartamento', 'casa', 'terreno', 'comercial',
        ];

        $condicoes = ['novo', 'usado', 'lancamento'];
        $statuses = ['ativo', 'inativo', 'reservado', 'vendido'];
        $cidades = ['São Paulo','Rio de Janeiro','Belo Horizonte','Curitiba','Porto Alegre','Florianópolis','Salvador','Fortaleza','Recife','Brasília'];
        
        // Tipos de anúncio disponíveis
        $anuncioTipos = ['Google_ads', 'Instagram_ads', 'Whatsapp_campaign', 'Site_anuncio'];

        // Create 20 properties with varied attributes
        for ($i = 1; $i <= 20; $i++) {
            $categoria = $categorias[($i - 1) % count($categorias)];
            $condicao = $condicoes[$i % count($condicoes)];
            $status = $statuses[($i - 1) % count($statuses)];
            $cidade = $cidades[($i - 1) % count($cidades)];

            $valor_venda = rand(100000, 2000000);
            $valor_locacao = rand(800, 15000);
            $quartos = rand(1, 6);
            $vagas = rand(1, 4);
            $area = rand(30, 600);
            // Definir finalidade real do imóvel (venda ou locação)
            $finalidade = rand(0, 1) ? 'venda' : 'locacao';

            // definir data aleatória nos últimos 60 dias para created_at/updated_at
            $eventDate = Carbon::now()->subDays(rand(0, 59))->setTime(rand(0,23), rand(0,59), rand(0,59));

            // Calcular e gravar a comissão com base na finalidade antes da criação
            $percent = ($finalidade === 'venda') ? rand(6, 12) : rand(12, 20);
            $comissaoBase = ($finalidade === 'venda') ? $valor_venda : $valor_locacao;
            $comissao_valor = round(($percent / 100) * $comissaoBase, 2);

            $imovel = Imovel::on('tenant_content')->create([
                'user_id' => $desiredUserId,
                'nome' => "Imóvel {$i} - {$categoria}",
                'codigo' => "SEED" . str_pad($i, 4, '0', STR_PAD_LEFT),
                'descricao' => "Descrição de exemplo para o imóvel {$i}",
                'endereco' => "Rua do Seeder, {$i}",
                'numero' => $i * 10,
                'cidade' => $cidade,
                'estado' => 'SP',
                'bairro' => "Bairro Seeder {$i}",
                'cep' => '00000-000',
                'complemento' => null,
                'referencia' => null,
                'mostrar_endereco_completo' => true,
                'categoria' => $categoria,
                'condicao' => $condicao,
                'status' => $status,
                'valor_venda' => $valor_venda,
                'valor_locacao' => $valor_locacao,
                'quartos' => $quartos,
                'vagas' => $vagas,
                'area_total' => $area,
                'area_construida' => intval($area * 0.7),
                'aceita_financiamento' => (bool) rand(0,1),
                'aceita_permuta' => (bool) rand(0,1),
                'finalidade' => $finalidade,
                // preencher comissão já calculada para evitar updates que alterem updated_at
                'comissao_percent' => $percent,
                'comissao_valor' => $comissao_valor,
                'created_at' => $eventDate,
                'updated_at' => $eventDate,
            ]);
            
            if (!empty($files)) {
                $img = $files[($i - 1) % count($files)];
                $filename = $imovel->id . '_' . $img->getFilename();

                // Copia arquivo físico para storage/app/public/imoveis/{filename}
                $source = $img->getPathname();
                $dest = storage_path('app/public/imoveis/' . $filename);
                File::copy($source, $dest);

                $imagemPath = 'imoveis/' . $filename;

                // Cria registro na tabela imovel_imagens
                ImovelImagem::on('tenant_content')->create([
                    'imovel_id' => $imovel->id,
                    'user_id' => $imovel->user_id,
                    'imagem_path' => $imagemPath,
                    'ordem' => 1,
                ]);
            }

            // Criar Listing (anúncio) para metade dos imóveis
            if ($i % 2 === 0) {
                // Selecionar 1 a 4 tipos de anúncio aleatoriamente
                $tiposCount = rand(1, 4);
                $tiposSelecionados = array_slice(
                    $anuncioTipos, 
                    0, 
                    $tiposCount
                );
                shuffle($tiposSelecionados);
                
                // Salvar como array (Eloquent fará o cast/JSON automaticamente)
                Listing::on('tenant_content')->create([
                    'imovel_id' => $imovel->id,
                    'anuncio_ativo' => true,
                    'anuncio_status' => 'active',
                    'anuncio_tipos' => $tiposSelecionados,
                ]);
            }

            // Criar autorização (proprietário) para o imóvel
            ImovelAutorizacao::on('tenant_content')->create([
                'imovel_id' => $imovel->id,
                'user_id' => $desiredUserId,
                'path' => null,
                'original_name' => null,
                'mime_type' => null,
                'size' => null,
                'checksum' => null,
                'uploaded_at' => now(),
                'proprietario_nome' => "Proprietário Seeder {$i}",
                'proprietario_telefone' => '11987654321',
                'proprietario_email' => "prop{$i}@example.com",
                'proprietario_documento' => '000.000.000-00',
            ]);

            // Criar planta para alguns imóveis
            if ($i % 5 === 0 && !empty($files)) {
                $plantaFile = $files[array_rand($files)];
                $plantaFilename = $imovel->id . '_planta_' . $plantaFile->getFilename();
                $sourcePlanta = $plantaFile->getPathname();
                $destPlanta = storage_path('app/public/imoveis/' . $plantaFilename);
                File::copy($sourcePlanta, $destPlanta);

                ImovelPlanta::on('tenant_content')->create([
                    'imovel_id' => $imovel->id,
                    'user_id' => $desiredUserId,
                    'path' => 'imoveis/' . $plantaFilename,
                    'original_name' => $plantaFile->getFilename(),
                    'mime_type' => File::mimeType($destPlanta) ?? 'application/octet-stream',
                    'size' => File::size($destPlanta),
                    'uploaded_at' => now(),
                ]);
            }

            // Criar midia (ex: registro de vídeo) apontando para a mesma imagem (opcional)
            if ($i % 7 === 0) {
                // Verifica se a tabela existe no banco do tenant antes de criar
                $schema = DB::connection('tenant_content')->getSchemaBuilder();
                if ($schema->hasTable('imovel_midias')) {
                    ImovelMidia::on('tenant_content')->create([
                        'imovel_id' => $imovel->id,
                        'user_id' => $desiredUserId,
                        'tipo' => 'video',
                        'path' => $imagemPath ?? '',
                        'ordem' => 1,
                    ]);
                } else {
                    $this->command->info("⚠️ Tabela 'imovel_midias' não encontrada para tenant {$tenantId}, pulando criação de midias.");
                }
            }

            $this->command->info("  ✅ Imóvel {$imovel->id} criado ({$categoria} / {$condicao})");
        }

        $this->command->info('✨ ImovelSeeder finalizado: 20 imóveis criados com imagens!');
    }

    /**
     * Obter tenant_id a partir do argumento ou variável global
     */
    private function getTenantId(): ?int
    {
        // Primeiro: verificar se foi setado via setTenantId()
        if ($this->tenantIdForced !== null) {
            return $this->tenantIdForced;
        }

        // Segundo: verificar se há variável global de tenant (caso rodado via middleware)
        if (function_exists('tenant') && tenant()) {
            return tenant()->id;
        }

        // Terceiro: perguntar ao usuário
        // Buscar tenants no banco central (`nexa_admin`) em vez da conexão tenant_content
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
        
        if (!is_numeric($tenantId)) {
            $this->command->error('❌ ID inválido');
            return null;
        }

        return (int) $tenantId;
    }

    /**
     * Configurar conexões do tenant
     */
    private function configureTenantConnections(int $tenantId): void
    {
        $credentialsDb = "tenant_{$tenantId}_credentials";
        $contentDb = "tenant_{$tenantId}_content";

        config([
            'database.connections.tenant_credentials.database' => $credentialsDb,
            'database.connections.tenant_content.database' => $contentDb,
        ]);

        // Reconectar
        DB::purge('tenant_credentials');
        DB::purge('tenant_content');

        // Verificar conexões
        try {
            DB::connection('tenant_credentials')->getPdo();
            $this->command->info("✅ Conectado a: {$credentialsDb}");
        } catch (\Exception $e) {
            $this->command->error("❌ Erro ao conectar em {$credentialsDb}: {$e->getMessage()}");
            throw $e;
        }

        try {
            DB::connection('tenant_content')->getPdo();
            $this->command->info("✅ Conectado a: {$contentDb}");
        } catch (\Exception $e) {
            $this->command->error("❌ Erro ao conectar em {$contentDb}: {$e->getMessage()}");
            throw $e;
        }
    }
}
