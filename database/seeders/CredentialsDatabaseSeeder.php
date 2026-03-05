<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\IntegracaoPagamento;

class CredentialsDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
        {
        $this->call([
            CargoSeeder::class,
            RolePermissaoSeeder::class,
            // EmpresaSeeder::class,
            // PlataformaSeeder::class,
        ]);

        // Admin master (sem empresa - acesso total)
        // $adminExists = User::where('email', 'admin@teste.com')->exists();

        // if (!$adminExists) {
        //     User::factory()->create([
        //         'name' => 'Admin Master',
        //         'email' => 'admin@teste.com',
        //         'password' => bcrypt('123456789'),
        //         'cargo_id' => 1,
        //     ]);
        // }

        // // Admin do E-commerce (vinculado à empresa 1)
        // $ecommerceAdminExists = User::where('email', 'ecommerce@teste.com')->exists();

        // if (!$ecommerceAdminExists) {
        //     User::factory()->create([
        //         'name' => 'Admin E-commerce',
        //         'email' => 'ecommerce@teste.com',
        //         'password' => bcrypt('123456789'),
        //         'cargo_id' => 1,
        //         'empresa_id' => 1,
        //     ]);
        // }

        // // Admin da Clínica (vinculado à empresa 2)
        // $clinicaAdminExists = User::where('email', 'clinica@teste.com')->exists();

        // if (!$clinicaAdminExists) {
        //     User::factory()->create([
        //         'name' => 'Admin Clínica',
        //         'email' => 'clinica@teste.com',
        //         'password' => bcrypt('123456789'),
        //         'cargo_id' => 1,
        //         'empresa_id' => 2,
        //     ]);
        // }

        // // Admin do Corretor (vinculado à empresa 3)
        // $corretorAdminExists = User::where('email', 'corretor@teste.com')->exists();

        // if (!$corretorAdminExists) {
        //     User::factory()->create([
        //         'name' => 'Admin Corretor',
        //         'email' => 'corretor@teste.com',
        //         'password' => bcrypt('123456789'),
        //         'cargo_id' => 1,
        //         'empresa_id' => 3,
        //     ]);
        // }

        IntegracaoPagamento::firstOrCreate(
          ['empresa_id' => 1],
        );

    }

    public function runTenantSpecificSeeders()
    {
    }
}
