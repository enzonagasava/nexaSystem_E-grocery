<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Garantir que cargos existam antes de criar usuários que dependem deles
        $this->call([
            CargoSeeder::class,
            EmpresaSeeder::class,
            PlataformaSeeder::class,
        ]);

        $adminExists = User::where('email', 'admin@teste.com')->exists();

        if (!$adminExists) {
            User::factory()->create([
                'name' => 'admin',
                'email' => 'admin@teste.com',
                'password' => bcrypt('123456789'), // Sempre use bcrypt para senha
                'cargo_id' => 1,
            ]);
        }
    }
}
