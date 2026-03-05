<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\Imovel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Listing>
 */
class ListingFactory extends Factory
{
    protected $model = Listing::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'imovel_id' => Imovel::factory(),
            'anuncio_ativo' => $this->faker->boolean(80),
            'anuncio_status' => $this->faker->randomElement(['ativo', 'pendente', 'pausado', null]),
        ];
    }

    /**
     * Indicate that the listing is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'anuncio_ativo' => true,
            'anuncio_status' => 'ativo',
        ]);
    }

    /**
     * Indicate that the listing is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'anuncio_ativo' => false,
            'anuncio_status' => 'pausado',
        ]);
    }
}
