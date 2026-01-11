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
        // Garantir que cargos existam antes de criar usuários que dependem deles
        $this->call([
            CargoSeeder::class,
            EmpresaSeeder::class,
            PlataformaSeeder::class,
        ]);

        // Admin master (sem empresa - acesso total)
        $adminExists = User::where('email', 'admin@teste.com')->exists();

        if (!$adminExists) {
            User::factory()->create([
                'name' => 'Admin Master',
                'email' => 'admin@teste.com',
                'password' => bcrypt('123456789'),
                'cargo_id' => 1,
                'empresa_id' => null, // Admin master sem empresa
            ]);
        }

        // Admin do E-commerce (vinculado à empresa 1)
        $ecommerceAdminExists = User::where('email', 'ecommerce@teste.com')->exists();

        if (!$ecommerceAdminExists) {
            User::factory()->create([
                'name' => 'Admin E-commerce',
                'email' => 'ecommerce@teste.com',
                'password' => bcrypt('123456789'),
                'cargo_id' => 1,
                'empresa_id' => 1, // Familia Mogi (e-commerce)
            ]);
        }

        // Admin da Clínica (vinculado à empresa 2)
        $clinicaAdminExists = User::where('email', 'clinica@teste.com')->exists();

        if (!$clinicaAdminExists) {
            User::factory()->create([
                'name' => 'Admin Clínica',
                'email' => 'clinica@teste.com',
                'password' => bcrypt('123456789'),
                'cargo_id' => 1,
                'empresa_id' => 2, // Clínica
            ]);
        }
    }
}
