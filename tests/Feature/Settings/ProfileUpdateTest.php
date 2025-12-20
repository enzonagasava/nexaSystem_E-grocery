<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

// NÃO usar RefreshDatabase - ele não funciona com múltiplos bancos

beforeEach(function () {
    // Limpar MANUALMENTE a tabela users antes de cada teste
    DB::connection('credentials')->table('users')->delete();
    
    // Resetar auto-increment no SQLite
    try {
        DB::connection('credentials')->statement('DELETE FROM sqlite_sequence WHERE name="users"');
    } catch (\Exception $e) {
        // Ignorar erro se não existir
    }
});

test('profile page is displayed', function () {
    $user = User::factory()->create([
        'email' => 'user_' . uniqid() . '@example.com',
    ]);

    $response = $this
        ->actingAs($user)
        ->get('/config/geral');

    $response->assertOk();
});

test('profile information can be updated', function () {
    // Email ORIGINAL único
    $originalEmail = 'original_' . uniqid() . '@example.com';
    
    $user = User::factory()->create([
        'email' => $originalEmail,
    ]);

    // Email NOVO único (DIFERENTE do original)
    $novoEmail = 'novo_' . uniqid() . '@example.com';
    
    $response = $this
        ->actingAs($user)
        ->patch('/config/geral', [
            'name' => 'Test User',
            'email' => $novoEmail, // ← EMAIL NOVO E ÚNICO
            'numero' => '(11) 12345-6789'
        ]);

    // DEBUG se falhar
    if (session()->has('errors')) {
        dd([
            'errors' => session('errors'),
            'existing_emails' => User::pluck('email')->toArray(),
            'user_id' => $user->id,
        ]);
    }
    
    $response->assertSessionHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe($novoEmail);
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $email = 'same_' . uniqid() . '@example.com';
    
    $user = User::factory()->create([
        'email' => $email,
    ]);

    $response = $this
        ->actingAs($user)
        ->patch('/config/geral', [
            'name' => 'Test User',
            'email' => $email, // MESMO email
            'numero' => '(11) 12345-6789',
        ]);

    $response->assertSessionHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});