<?php

namespace Database\Seeders;

use App\Enums\TipoEmpresa;
use App\Models\Empresa;
use App\Models\RedeSocial;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
<<<<<<< HEAD
        // Busca os tipos do outro banco
        $tipoEcommerce = DB::connection('nexa_admin')
            ->table('tipo_painel')
            ->where('nome', 'CRM Ecommerce')
            ->first();
            
        $tipoClinica = DB::connection('nexa_admin')
            ->table('tipo_painel')
            ->where('nome', 'CRM Clínica')
            ->first();
            
        $tipoCorretor = DB::connection('nexa_admin')
            ->table('tipo_painel')
            ->where('nome', 'CRM Imobiliário')
            ->first();
        
        // Empresa principal - E-commerce
=======
        // Empresa principal - E-commerce (padrão do sistema original)
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
        Empresa::firstOrCreate(
            ['id' => 1],
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
<<<<<<< HEAD
                'tipo_painel_id' => $tipoEcommerce->id ?? null,
=======
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
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
            ]
        );

        logger()->info('EmpresaSeeder: Empresa principal criada ou já existente.');
        
            Empresa::firstOrCreate(
            ['id' => 2],
            [
                'nome' => 'Corretor',
                'email' => 'corretor@gmail.com',
                'numero_wpp' => '(11) 99999-9999',
                'telefone' => '(11) 3333-3333',
                'cnpj' => '00.000.000/0001-00',
                'endereco' => 'Rua Exemplo, 123 - Centro',
                'cep' => '00000-000',
                'numero_endereco' => '123',
                'municipio' => 'São Paulo',
                'estado' => 'SP',
                'tipo_painel_id' => $tipoCorretor->id ?? null,
            ]
        );

        logger()->info('EmpresaSeeder: Empresa corretor criada ou já existente.');

            Empresa::firstOrCreate(
            ['id' => 3],
            [
                'nome' => 'Clínica',
                'email' => 'clinica@gmail.com',
                'numero_wpp' => '(11) 99999-9999',
                'telefone' => '(11) 3333-3333',
                'cnpj' => '00.000.000/0001-00',
                'endereco' => 'Rua Exemplo, 123 - Centro',
                'cep' => '00000-000',
                'numero_endereco' => '123',
                'municipio' => 'São Paulo',
                'estado' => 'SP',
                'tipo_painel_id' => $tipoClinica->id ?? null,
            ]
        );

        logger()->info('EmpresaSeeder: Empresa clínica criada ou já existente.');
    }
}