<?php

namespace Database\Seeders;

use App\Enums\TipoEmpresa;
use App\Models\Empresa;
use App\Models\RedeSocial;
use App\Models\IntegracaoPagamento;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmpresaSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Empresa principal - E-commerce (padrão do sistema original)
        Empresa::firstOrCreate(
            ['id' => 1], // garante que só uma empresa seja criada
            [
                'nome' => 'Familia Mogi',
                'email' => 'familiamogi@gmail.com',
                'numero_wpp' => '(11) 99999-9999',
                'telefone' => '(11) 3333-3333',
                'cnpj' => '00.000.000/0001-00',
                'endereco' => 'Rua Exemplo, 123 - Centro',
                'cep' => '00000-000',
                'numero_endereco' => '123',
                'municipio' => 'São Paulo',
                'estado' => 'SP',
                'tipo' => TipoEmpresa::Ecommerce,
            ]
        );

        // Empresa de exemplo - Clínica Médica
        Empresa::firstOrCreate(
            ['id' => 2],
            [
                'nome' => 'Clínica Saúde Total',
                'email' => 'contato@clinicasaudetotal.com.br',
                'numero_wpp' => '(11) 98888-8888',
                'telefone' => '(11) 4444-4444',
                'cnpj' => '11.111.111/0001-11',
                'endereco' => 'Av. Saúde, 456 - Centro',
                'cep' => '11111-111',
                'numero_endereco' => '456',
                'municipio' => 'São Paulo',
                'estado' => 'SP',
                'tipo' => TipoEmpresa::Clinica,
            ]
        );

        // Empresa de exemplo - Corretor de Imóveis
        Empresa::firstOrCreate(
            ['id' => 3],
            [
                'nome' => 'Imobiliária Prime',
                'email' => 'contato@imobiliariaprime.com.br',
                'numero_wpp' => '(11) 97777-7777',
                'telefone' => '(11) 5555-5555',
                'cnpj' => '22.222.222/0001-22',
                'endereco' => 'Rua dos Imóveis, 789 - Centro',
                'cep' => '22222-222',
                'numero_endereco' => '789',
                'municipio' => 'São Paulo',
                'estado' => 'SP',
                'tipo' => TipoEmpresa::Corretor,
            ]
        );

        RedeSocial::firstOrCreate(
          ['id' => 1],
        );

        IntegracaoPagamento::firstOrCreate(
          ['empresa_id' => 1],
        );
    }
}
