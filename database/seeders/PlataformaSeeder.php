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
<<<<<<< HEAD
            DB::table('plataforma_pedido')->insertOrIgnore($plataforma);
=======
            DB::connection('content')->table('plataforma_pedido')->insertOrIgnore($plataforma);
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
        }
    }
}
