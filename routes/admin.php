<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\App\DashboardController as ClienteDashboardController;
use App\Http\Controllers\Admin\ProdutoController;
use App\Http\Controllers\Admin\PedidoController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::post('/dashboardRota', [AdminDashboardController::class, 'dashboardRota'])->middleware('jwt.cookie', 'auth')->name('dashboardRota');

Route::middleware(['jwt.cookie','auth','cliente'])->prefix('cliente')->name('cliente.')->group(function () {
    Route::get('/dashboard', [ClienteDashboardController::class, 'index'])->name('dashboard');
    // rotas cliente...
});

// Rotas do módulo E-commerce
Route::middleware(['jwt.cookie', 'auth', 'tipo:ecommerce'])
    ->prefix('admin/ecommerce')
    ->name('admin.ecommerce.')
    ->group(base_path('routes/modulos/ecommerce.php'));

// Rotas do módulo Clínica Médica
Route::middleware(['jwt.cookie', 'auth', 'tipo:clinica'])
    ->prefix('admin/clinica')
    ->name('admin.clinica.')
    ->group(base_path('routes/modulos/clinica.php'));

// Rotas do módulo Corretor (Imobiliário)
Route::middleware(['jwt.cookie', 'auth', 'tipo:corretor'])
    ->prefix('admin/corretor')
    ->name('admin.corretor.')
    ->group(base_path('routes/modulos/corretor.php'));
