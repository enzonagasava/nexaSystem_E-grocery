<?php

use App\Http\Controllers\Admin\Corretor\DashboardController;
use App\Http\Controllers\Admin\Corretor\ImovelController;
use App\Http\Controllers\Admin\Corretor\LeadController;
use App\Http\Controllers\Admin\Corretor\ContatoController;
use App\Http\Controllers\Admin\Corretor\FinanceiroController;
use App\Http\Controllers\Admin\Corretor\EmailController;
use App\Http\Controllers\Admin\Corretor\RelatorioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas do Módulo Corretor de Imóveis
|--------------------------------------------------------------------------
|
| Rotas específicas para o módulo de corretores de imóveis.
| Middleware aplicado no grupo pai: jwt.cookie, auth
| Prefixo: admin/corretor
| Nome: admin.corretor.*
|
*/

Route::middleware(['tipo:corretor'])->group(function () {
    // Dashboard do corretor
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Imóveis
    Route::resource('imoveis', ImovelController::class);

    // Leads
    Route::resource('leads', LeadController::class);

    // Contatos
    Route::resource('contatos', ContatoController::class);

    // Crédito & Financiamentos
    Route::resource('financeiro', FinanceiroController::class);

    // Email Marketing
    Route::resource('emails', EmailController::class);

    // Relatórios
    Route::get('/relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
    Route::get('/relatorios/vendas', [RelatorioController::class, 'vendas'])->name('relatorios.vendas');
    Route::get('/relatorios/leads', [RelatorioController::class, 'leads'])->name('relatorios.leads');
    Route::get('/relatorios/financeiro', [RelatorioController::class, 'financeiro'])->name('relatorios.financeiro');
});
