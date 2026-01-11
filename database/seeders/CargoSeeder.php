<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CargoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conn = 'credentials';

        if (! Schema::connection($conn)->hasTable('cargos')) {
            $this->command->info("Tabela 'cargos' não existe na conexão '{$conn}', pulando seeder.");
            return;
        }

        // Desabilita FK temporariamente (para evitar erros de ordem)
        DB::connection($conn)->statement('SET FOREIGN_KEY_CHECKS=0;');

        // Trunca a tabela
        DB::connection($conn)->table('cargos')->truncate();

        // Reabilita FK
        DB::connection($conn)->statement('SET FOREIGN_KEY_CHECKS=1;');

        // Insere cargos com IDs EXPLÍCITOS (importante!)
        DB::connection($conn)->table('cargos')->insert([
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
