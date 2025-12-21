<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlataformaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Desabilita FK temporariamente
        DB::connection('content')->statement('SET FOREIGN_KEY_CHECKS=0;');

        // Trunca a tabela (opcional)
        DB::connection('content')->table('plataforma_pedido')->truncate();

        // Reabilita FK
        DB::connection('content')->statement('SET FOREIGN_KEY_CHECKS=1;');

        $items = [
            ['nome' => 'Shopee'],
            ['nome' => 'Mercado Livre'],
            ['nome' => 'Amazon'],
            ['nome' => 'Loja Integrada'],
        ];

        // evita exception de unique quando já existe
        DB::connection('content')->table('plataforma_pedido')->insertOrIgnore($items);

        $this->command->info('Plataformas seedadas com sucesso.');
    }
}
