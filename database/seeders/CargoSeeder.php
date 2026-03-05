<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CargoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
<<<<<<< HEAD
        // Insere cargos com IDs EXPLÍCITOS (importante!)
        DB::table('cargos')->insert([
=======
        // Desabilita FK temporariamente (para evitar erros de ordem)
        DB::connection('credentials')->statement('SET FOREIGN_KEY_CHECKS=0;');

        // Trunca a tabela
        DB::connection('credentials')->table('cargos')->truncate();

        // Reabilita FK
        DB::connection('credentials')->statement('SET FOREIGN_KEY_CHECKS=1;');

        // Insere cargos com IDs EXPLÍCITOS (importante!)
        DB::connection('credentials')->table('cargos')->insert([
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
            [
                'id' => 1, // ← ID 1 para admin
                'nome' => 'admin',
                'descricao' => 'Administrador do sistema',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2, // ← ID 2 para cliente
                'nome' => 'cliente',
                'descricao' => 'Usuário cliente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3, // ← ID 3 para vendedor
                'nome' => 'vendedor',
                'descricao' => 'Vendedor/Atendente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4, // ← ID 4 para gerente
                'nome' => 'gerente',
                'descricao' => 'Gerente de vendas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->command->info('Cargos seedados com sucesso: admin(1), cliente(2), vendedor(3), gerente(4)');
    }
}
