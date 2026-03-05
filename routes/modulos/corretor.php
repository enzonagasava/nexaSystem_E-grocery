<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Corretor\DashboardController;
use App\Http\Controllers\Admin\Corretor\ListingsController;
use App\Http\Controllers\Admin\Corretor\SettingsController;
use App\Http\Controllers\Admin\Corretor\LeadsController;
use App\Http\Controllers\Admin\Corretor\ContatosController;
use App\Http\Controllers\Admin\Corretor\ImoveisController;
use App\Http\Controllers\Admin\Corretor\KanbanController;
use App\Http\Controllers\Admin\Corretor\RelatoriosController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\ChatSettingsController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\InfoEmpresaController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Menu (lista de itens do sidebar)
Route::get('menu', function () {
    return \Inertia\Inertia::render('admin/MenuIndex');
})->name('menu.index');

// Calendário — permissão agenda.visualizar
Route::middleware(['permissao:agenda.visualizar'])->group(function () {
    Route::get('calendar', [\App\Http\Controllers\Admin\CalendarController::class, 'index'])->name('calendar.index');
});

// Listings (anúncios) e Imóveis — permissão imoveis.visualizar
Route::middleware(['permissao:imoveis.visualizar'])->group(function () {
    Route::get('listings', [ListingsController::class, 'index'])->name('listings.index');
    Route::get('listings/create', [ListingsController::class, 'create'])->name('listings.create');
    Route::post('listings', [ListingsController::class, 'store'])->name('listings.store');
    Route::get('listings/{id}', [ListingsController::class, 'show'])->name('listings.show');
    Route::get('listings/{id}/edit', [ListingsController::class, 'edit'])->name('listings.edit');
    Route::put('listings/{id}', [ListingsController::class, 'update'])->name('listings.update');
    Route::delete('listings/{id}', [ListingsController::class, 'destroy'])->name('listings.destroy');

    Route::get('imoveis', [ImoveisController::class, 'index'])->name('imoveis.index');
    Route::get('imoveis/cidades', [ImoveisController::class, 'getCidades'])->name('imoveis.cidades');
    Route::get('imoveis/categorias', [ImoveisController::class, 'getCategorias'])->name('imoveis.categorias');
    Route::get('imoveis/create', [ImoveisController::class, 'create'])->name('imoveis.create');
    Route::post('imoveis', [ImoveisController::class, 'store'])->name('imoveis.store');
    Route::get('imoveis/{id}', [ImoveisController::class, 'show'])->name('imoveis.show');
    Route::get('imoveis/{id}/edit', [ImoveisController::class, 'edit'])->name('imoveis.edit');
    Route::put('imoveis/{id}', [ImoveisController::class, 'update'])->name('imoveis.update');
    Route::post('imoveis/{id}', [ImoveisController::class, 'update'])->name('imoveis.update.post');
    Route::delete('imoveis/{id}', [ImoveisController::class, 'destroy'])->name('imoveis.destroy');
    Route::get('imoveis/{id}/autorizacao/{auth}', [ImoveisController::class, 'downloadAutorizacao'])->name('imoveis.autorizacao.download');
    Route::get('imoveis/{id}/planta/{planta}', [ImoveisController::class, 'downloadPlanta'])->name('imoveis.planta.download');
    Route::get('imoveis/{id}/video/{video}', [ImoveisController::class, 'streamVideo'])->name('imoveis.video.stream');
    Route::delete('imoveis/{id}/images/{imageId}', [ImoveisController::class, 'deleteImage'])->name('imoveis.image.delete');
    Route::delete('imoveis/{id}/videos/{videoId}', [ImoveisController::class, 'deleteVideo'])->name('imoveis.video.delete');
    Route::delete('imoveis/{id}/plants/{plantaId}', [ImoveisController::class, 'deletePlanta'])->name('imoveis.planta.delete');
    Route::delete('imoveis/{id}/authorizations/{authId}', [ImoveisController::class, 'deleteAutorizacao'])->name('imoveis.autorizacao.delete');
});

// Chat — permissão chat.visualizar
Route::middleware(['permissao:chat.visualizar'])->group(function () {
    Route::get('chat', [ChatController::class, 'index'])->name('chat');
    Route::get('chat/conversations', [ChatController::class, 'getConversations'])->name('chat.conversations');
    Route::get('chat/messages', [ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::post('chat/mark-read', [ChatController::class, 'markAsRead'])->name('chat.markRead');
    Route::get('chat/settings', [ChatSettingsController::class, 'index'])->name('chat.settings');
    Route::put('chat/settings/config', [ChatSettingsController::class, 'updateConfig'])->name('chat.settings.config');
    Route::post('chat/settings/respostas-rapidas', [ChatSettingsController::class, 'storeRespostaRapida'])->name('chat.settings.respostas.store');
    Route::put('chat/settings/respostas-rapidas/{respostaRapida}', [ChatSettingsController::class, 'updateRespostaRapida'])->name('chat.settings.respostas.update');
    Route::delete('chat/settings/respostas-rapidas/{respostaRapida}', [ChatSettingsController::class, 'destroyRespostaRapida'])->name('chat.settings.respostas.destroy');
});

// Settings / profile
Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

// Kanban e Leads — permissão leads.visualizar
Route::middleware(['permissao:leads.visualizar'])->group(function () {
    Route::get('kanban', [KanbanController::class, 'index'])->name('kanban.index');
    Route::put('/kanban/{id}/status', [KanbanController::class, 'update'])->name('kanban.update-status');
    Route::post('/kanban/bulk-status', [KanbanController::class, 'bulkUpdate'])->name('kanban.bulk-status');
    Route::get('kanban/quadros/{id}/dados', [KanbanController::class, 'getQuadroInfo'])->name('kanban.quadro.dados');
    Route::get('kanban/quadros/{quadroId}/colunas', [KanbanController::class, 'getColunas'])->name('kanban.colunas.index');
    Route::post('kanban/colunas', [KanbanController::class, 'storeColuna'])->name('kanban.colunas.store');
    Route::put('kanban/colunas/{id}', [KanbanController::class, 'updateColuna'])->name('kanban.colunas.update');
    Route::delete('kanban/colunas/{id}', [KanbanController::class, 'destroyColuna'])->name('kanban.colunas.destroy');
    Route::post('kanban/colunas/reordenar', [KanbanController::class, 'reordenarColunas'])->name('kanban.colunas.reordenar');
    Route::post('kanban/colunas/ordem', [KanbanController::class, 'storeOrdemColunas'])->name('kanban.colunas.reordenar');

    Route::get('leads', [LeadsController::class, 'index'])->name('leads.index');
    Route::get('leads/create', [LeadsController::class, 'create'])->name('leads.create');
    Route::post('leads', [LeadsController::class, 'store'])->name('leads.store');
    Route::put('leads/{id}', [LeadsController::class, 'update'])->name('leads.update');
    Route::get('leads/{id}/edit', [LeadsController::class, 'edit'])->name('leads.edit');
    Route::get('leads/{id}/show', [LeadsController::class, 'show'])->name('leads.show');
    Route::delete('leads/{id}', [LeadsController::class, 'destroy'])->name('leads.destroy');
    Route::post('/leads/{id}/converter', [LeadsController::class, 'converterLeadParaContato'])->name('leads.converter');
});

// Contatos (sem módulo de permissão no seeder; sempre visível para tipo corretor)
Route::get('contatos', [ContatosController::class, 'index'])->name('contatos.index');
Route::get('contatos/create', [ContatosController::class, 'create'])->name('contatos.create');
Route::post('contatos', [ContatosController::class, 'store'])->name('contatos.store');
Route::get('contatos/{id}/edit', [ContatosController::class, 'edit'])->name('contatos.edit');
Route::get('contatos/{id}/show', [ContatosController::class, 'show'])->name('contatos.show');
Route::put('contatos/{id}', [ContatosController::class, 'update'])->name('contatos.update');
Route::delete('contatos/{id}', [ContatosController::class, 'destroy'])->name('contatos.destroy');

// Tabs: Crédito & Financiamento, eAssinaturas & Documentos (placeholders)
Route::get('credito-financiamento', function () { return Inertia\Inertia::render('admin/corretor/CreditoFinanciamento'); })->name('credito.index');
Route::get('assinaturas-documentos', function () { return Inertia\Inertia::render('admin/corretor/AssinaturasDocumentos'); })->name('assinaturas.index');

// Relatórios (sem permissão por enquanto)
Route::get('relatorios', [RelatoriosController::class, 'index'])->name('relatorios.index');

    //Configuração de Acesso
    Route::get('config/geral', [ProfileController::class, 'edit'])->name('config.geral');
    Route::patch('config/geral', [ProfileController::class, 'update'])->name('config.update');
    Route::delete('config/geral', [ProfileController::class, 'destroy'])->name('config.destroy');

    //Configuração da Informação da Empresa
    Route::prefix('empresa/config')->middleware(['auth'])->group(function () {
        Route::get('/geral', [InfoEmpresaController::class, 'geral'])->name('empresa.config.geral');
        Route::patch('/geral', [InfoEmpresaController::class, 'updateGeral'])->name('empresa.config.update.geral');

        Route::get('/logo', [InfoEmpresaController::class, 'Logo'])->name('empresa.config.logo');
        Route::post('/logo', [InfoEmpresaController::class, 'updateLogo'])->name('empresa.config.update.logo');

        Route::get('/redes-sociais', [InfoEmpresaController::class, 'RedesSociais'])->name('empresa.config.redes');
        Route::patch('/redes-sociais', [InfoEmpresaController::class, 'updateRedes'])->name('empresa.config.update.redes');

    });
