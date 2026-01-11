<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlataformaSeeder extends Seeder
{
    public function run(): void
    {
        $conn = 'content';

        if (! Schema::connection($conn)->hasTable('plataforma_pedido')) {
            $this->command->info("Tabela 'plataforma_pedido' não existe na conexão '{$conn}', pulando seeder.");
            return;
        }

        $names = ['Shopee', 'Mercado Livre', 'Amazon', 'Loja Integrada'];
        $now = now();

        foreach ($names as $name) {
            DB::connection($conn)->table('plataforma_pedido')->updateOrInsert(
                ['nome' => $name],
                ['nome' => $name, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $this->command->info('Plataformas seedadas/atualizadas com sucesso.');
    }
}
