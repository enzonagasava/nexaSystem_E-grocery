<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CartController; 
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\ProdutoController;
use App\Http\Controllers\PaymentController;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    $host = request()->getHost();
    $mainDomain = env('MAIN_SUBDOMAIN', 'localhost');

    // Se for o domínio principal, retorna landing page
    if ($host === $mainDomain) {
        return Inertia::render('Landing');
    }

    // Verifica se há um tenant configurado
    $tenant = Tenant::current();
    
    if (!$tenant) {
        Log::error("❌ Rota / - Nenhum tenant configurado para host: {$host}");
        
        // Retorna página de erro amigável ou redireciona
        return response()->view('errors.404', [
            'message' => 'Loja não encontrada. Verifique o endereço e tente novamente.'
        ], 404);
    }
    
    Log::info("✅ Rota / - Tenant configurado: ID={$tenant->id}, renderizando Home");
    return app(App\Http\Controllers\HomeController::class)->index();
})->name('home');

Route::get('/corretor', function () {
    return Inertia::render('LandingCorretor');
})->name('landing.corretor');

// Rota de health check para debug
Route::get('/health', function () {
    $host = request()->getHost();
    $tenant = Tenant::current();
    
    return response()->json([
        'status' => 'ok',
        'host' => $host,
        'tenant' => $tenant ? [
            'id' => $tenant->id,
            'nome' => $tenant->nome,
            'subdominio' => $tenant->subdominio,
        ] : null,
        'session_driver' => config('session.driver'),
        'tenant_connections' => [
            'credentials' => config('database.connections.tenant_credentials.database'),
            'content' => config('database.connections.tenant_content.database'),
        ]
    ]);
})->name('health');

Route::get('/anuncio/{id}', [ProdutoController::class, 'anuncio'])->name('anuncio');
Route::get('/produtos', [ProdutoController::class, 'show'])->name('produtos.index');

Route::get('/about-us', function () {
    return Inertia::render('AboutUs');
})->name('about.us');

Route::get('/how-to-buy', function () {
    return Inertia::render('HowToBuy');
})->name('how.to.buy');

Route::get('/contact', function () {
    return Inertia::render('Contact');
})->name('contact');

Route::get('/carrinho', [CartController::class, 'index'])->name('carrinho.index');
Route::post('/carrinho/adicionar', [CartController::class, 'adicionar'])->name('carrinho.adicionar');
Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
Route::delete('/carrinho/remover/{cartItemId}', [CartController::class, 'remover'])->name('carrinho.remover');

Route::get('/pagepay', [PaymentController::class, 'index'])->name('payment.index');
Route::get('/checkout/sucesso', function () {
    return Inertia::render('App/Checkout/Success', [
        'paymentId' => request('payment_id'),
        'status' => request('status'),
    ]);
})->name('checkout.sucesso');

Route::get('/checkout/falha', function () {
    return Inertia::render('App/Checkout/Failure', [
        'status' => request('status'),
        'message' => request('message'),
    ]);
})->name('checkout.falha');

Route::get('/checkout/pendente', function () {
    return Inertia::render('App/Checkout/Pending', [
        'paymentId' => request('payment_id'),
        'status' => request('status'),
    ]);
})->name('checkout.pendentes');


Route::get('/api/pedido/status/{id}', function ($id) {
    return \App\Models\GerenciarPedido::findOrFail($id);
});



require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
