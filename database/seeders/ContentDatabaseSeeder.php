<?php

namespace Database\Seeders;

use App\Models\ConfiguracaoIa;
use Illuminate\Database\Seeder;


class ContentDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
        {
        $this->call([
            // EmpresaSeeder::class,
            PlataformaSeeder::class,
            StatusSeeder::class,
            KanbanSeeder::class
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


    }

}
