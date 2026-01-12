<?php

use App\Http\Controllers\Admin\Ecommerce\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas do Módulo E-commerce
|--------------------------------------------------------------------------
|
| Rotas específicas para empresas do tipo e-commerce.
| Middleware: jwt.cookie, auth, tipo:ecommerce
| Prefixo: /admin/ecommerce
| Nome: admin.ecommerce.
|
*/

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Futuras rotas específicas de e-commerce:
// Route::resource('pedidos', PedidoController::class);
// Route::resource('produtos', ProdutoController::class);
// Route::resource('carrinho', CarrinhoController::class);
