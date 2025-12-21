<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CartController; 
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\ProdutoController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\App\CheckoutController;

Route::get('/', [HomeController::class, 'index'])->name('home');
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

