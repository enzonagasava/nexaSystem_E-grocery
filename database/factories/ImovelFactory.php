<?php

namespace Database\Factories;

use App\Models\Imovel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImovelFactory extends Factory
{
    protected $model = Imovel::class;

    public function definition()
    {
        return [
            'user_id' => null,
            'codigo' => strtoupper($this->faker->bothify('IMV-###??')),
            'nome' => $this->faker->sentence(3),
            'descricao' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['ativo','inativo','reservado','vendido']),
            'categoria' => $this->faker->randomElement(['apartamento','casa','terreno','comercial']),
            'finalidade' => $this->faker->randomElement(['venda','locacao']),
            'modalidade' => $this->faker->randomElement(['venda','aluguel']),
            'condicao' => $this->faker->randomElement(['novo','usado','reforma']),
            'exclusividade' => $this->faker->boolean(20),
            'cep' => $this->faker->postcode(),
            'estado' => $this->faker->stateAbbr(),
            'cidade' => $this->faker->city(),
            'bairro' => $this->faker->word(),
            'endereco' => $this->faker->streetAddress(),
            'numero' => (string) $this->faker->numberBetween(1, 9999),
            'valor_venda' => $this->faker->randomFloat(2, 50000, 1500000),
            'valor_locacao' => $this->faker->randomFloat(2, 500, 10000),
            'quartos' => $this->faker->numberBetween(1,5),
            'suites' => $this->faker->numberBetween(0,3),
            'banheiros' => $this->faker->numberBetween(1,4),
            'vagas' => $this->faker->numberBetween(0,4),
            'area_construida' => $this->faker->randomFloat(2, 30, 400),
            'proprietario_nome' => $this->faker->name(),
            'proprietario_telefone' => $this->faker->phoneNumber(),
            'proprietario_email' => $this->faker->safeEmail(),
        ];
    }
}
