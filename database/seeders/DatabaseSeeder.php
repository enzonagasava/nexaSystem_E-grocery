<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. PRIMEIRO: Seed cargos (credentials)
        $this->call(CargoSeeder::class);

        // 2. DEPOIS: Criar usuário admin (que referencia cargo)
        $adminExists = User::on('credentials')
            ->where('email', 'admin@teste.com')
            ->exists();

        if (!$adminExists) {
            User::on('credentials')->create([
                'name' => 'admin',
                'email' => 'admin@teste.com',
                'password' => bcrypt('123456789'),
                'cargo_id' => 1, // ID do cargo 'admin' que acabou de ser criado
            ]);
            $this->command->info('Admin user created successfully.');
        }

        // 3. POR FIM: Seeders do banco content
        $this->call([
            EmpresaSeeder::class,
            PlataformaSeeder::class,
            CargoSeeder::class,
            // Outros seeders do content
        ]);
    }
}
