<?php

namespace Tests\Feature;

use App\Models\Cargo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CargoCreationTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
public function it_can_create_a_cargo()
{
    // USAR NOME ÚNICO SEMPRE
    $nomeUnico = 'Admin_' . uniqid();
    
    $cargo = Cargo::create([
            'nome' => $nomeUnico, // ← NUNCA usar "Admin" fixo
            'descricao' => 'Cargo de administrador do sistema',
        ]);
        
        $this->assertDatabaseHas('cargos', [
            'id' => $cargo->id,
            'nome' => $nomeUnico,
<<<<<<< HEAD
        ], 'tenant_credentials');
=======
        ], 'credentials');
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
    }
}
