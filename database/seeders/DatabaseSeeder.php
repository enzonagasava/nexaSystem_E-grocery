<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ConfiguracaoIa;
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

        ConfiguracaoIa::create([
            'bot_ativo' => false,
            'tom_voz' => 'amigavel',
            'mensagem_boas_vindas' => 'Olá! Seja bem-vindo(a)! Como posso ajudar?',
            'mensagem_fora_horario' => 'Obrigado por entrar em contato! No momento estamos fora do horário de atendimento. Retornaremos em breve.',
            'timer_ausencia' => 300,
            'bloquear_bot' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Admin master (sem empresa - acesso total)
        $adminExists = User::where('email', 'admin@teste.com')->exists();

        if (!$adminExists) {
            User::factory()->create([
                'name' => 'Admin Master',
                'email' => 'admin@teste.com',
                'password' => bcrypt('123456789'),
                'cargo_id' => 1,
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
            ]);
        }
    }
}
