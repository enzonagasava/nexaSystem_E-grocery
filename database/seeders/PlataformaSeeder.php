<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlataformaSeeder extends Seeder
{
    public function run(): void
    {
        $plataformas = [
            ['nome' => 'Shopee'],
            ['nome' => 'Mercado Livre'],
            ['nome' => 'Amazon'],
            ['nome' => 'Loja Integrada'],
        ];

        foreach ($plataformas as $plataforma) {
            DB::connection('content')->table('plataforma_pedido')->insertOrIgnore($plataforma);
        }
    }
}
