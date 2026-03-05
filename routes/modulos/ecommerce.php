<?php

use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\ChatSettingsController;
use App\Http\Controllers\Admin\Ecommerce\DashboardController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\InfoEmpresaController;
use App\Http\Controllers\Settings\PagamentoConfigController;
use App\Http\Controllers\Admin\ProdutoController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Admin\PedidoController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

// Menu (lista de itens do sidebar)
Route::get('menu', function () {
    return Inertia::render('admin/ecommerce/MenuIndex');
})->name('menu.index');



    Route::get('anuncio/config', function () {
        return Inertia::render('admin/ecommerce/AnuncioConfig');
    })->name('anuncio.config');


    Route::get('paginas/config', function () {
        return Inertia::render('admin/ecommerce/paginasConfig/PaginasConfig');
    })->name('paginas.config');

    Route::get('blog/config', function () {
        return Inertia::render('admin/ecommerce/BlogConfig');
    })->name('blog.config');

    //Produtos Configurações
    Route::get('produtos/config', [ProdutoController::class, 'index'])->name('produtos.config');
    Route::get('produtos/create-produto', [ProdutoController::class, 'create'])->name('produtos.create');
    Route::post('produtos/addprodutos', [ProdutoController::class, 'store'])->name('produtos.store');
    Route::get('produtos/edit-produto/{id}', [ProdutoController::class, 'edit'])->name('produtos.edit');
    Route::put('produtos/update-produto/{id}', [ProdutoController::class, 'update'])->name('produtos.update');
    Route::delete('produtos/delete-produto/{id}', [ProdutoController::class, 'destroy'])->name('produtos.destroy'); 

    //Rotas do Cliente
    Route::get('clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('clientes/adicionarCliente', [ClienteController::class, 'create'])->name('adicionar.clientes');
    Route::get('clientes/editarCliente/{id}', [ClienteController::class, 'edit'])->name('editar.clientes');

    Route::post('clientes/atualizarCliente/{id}', [ClienteController::class, 'update'])->name('atualizar.clientes');
    Route::post('clientes/adicionarCliente', [ClienteController::class, 'store'])->name('clientes.store');
    Route::delete('clientes/deletar-cliente/{id}', [ClienteController::class, 'destroy'])->name('clientes.destroy');
    Route::get('/clientes/buscar', [SearchController::class, 'buscarCliente'])->name('clientes.buscar');

    //Rotas Pedidos
    Route::put('/pedidos/avancarStatus/{id}', [PedidoController::class, 'avancarStatus'])
        ->name('pedidos.avancar.status');
    Route::get('/pedidos/adicionarPedido', [PedidoController::class, 'create'])->name('pedidos.create');
    Route::post('/pedidos/adicionarPedido', [PedidoController::class, 'store'])->name('pedidos.store');

    Route::get('/pedidos/{pedido}/editar', [PedidoController::class, 'edit'])
        ->name('pedidos.edit');
    Route::put('/pedidos/{pedido}/editar', [PedidoController::class, 'update'])
        ->name('pedidos.update');

    Route::get('/pedidos/{pedido}/visualizar', [PedidoController::class, 'view'])->name('pedidos.view');

    Route::get('/pedidos/buscarProduto', [SearchController::class, 'buscarProduto'])->name('pedidos.buscarProduto');

    Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');


// Calendário — permissão agenda.visualizar
Route::middleware(['permissao:agenda.visualizar'])->group(function () {
    Route::get('calendar', [\App\Http\Controllers\Admin\CalendarController::class, 'index'])->name('calendar.index');
    Route::get('calendar/events', [\App\Http\Controllers\Admin\CalendarController::class, 'events'])->name('calendar.events');
    Route::post('calendar/events', [\App\Http\Controllers\Admin\CalendarController::class, 'store'])->name('calendar.store');
    Route::put('calendar/events/{id}', [\App\Http\Controllers\Admin\CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('calendar/events/{id}', [\App\Http\Controllers\Admin\CalendarController::class, 'destroy'])->name('calendar.destroy');
    Route::get('calendar/settings', [\App\Http\Controllers\Admin\CalendarSettingsController::class, 'index'])->name('calendar.settings');
    Route::get('calendar/settings/data', [\App\Http\Controllers\Admin\CalendarSettingsController::class, 'data'])->name('calendar.settings.data');
    Route::put('calendar/settings', [\App\Http\Controllers\Admin\CalendarSettingsController::class, 'update'])->name('calendar.settings.update');
    Route::get('calendar/auth', [\App\Http\Controllers\Admin\GoogleCalendarAuthController::class, 'redirect'])->name('calendar.auth');
    Route::get('calendar/callback', [\App\Http\Controllers\Admin\GoogleCalendarAuthController::class, 'callback'])->name('calendar.callback');
    Route::post('calendar/disconnect', [\App\Http\Controllers\Admin\GoogleCalendarAuthController::class, 'disconnect'])->name('calendar.disconnect');
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

// Configuração de Acesso
Route::get('config/geral', [ProfileController::class, 'edit'])->name('config.geral');
Route::patch('config/geral', [ProfileController::class, 'update'])->name('config.update');
Route::delete('config/geral', [ProfileController::class, 'destroy'])->name('config.destroy');

// Configuração da Informação da Empresa
Route::prefix('empresa/config')->middleware(['auth'])->group(function () {
    Route::get('/geral', [InfoEmpresaController::class, 'geral'])->name('empresa.config.geral');
    Route::patch('/geral', [InfoEmpresaController::class, 'updateGeral'])->name('empresa.config.update.geral');
    Route::get('/logo', [InfoEmpresaController::class, 'Logo'])->name('empresa.config.logo');
    Route::post('/logo', [InfoEmpresaController::class, 'updateLogo'])->name('empresa.config.update.logo');
    Route::get('/redes-sociais', [InfoEmpresaController::class, 'RedesSociais'])->name('empresa.config.redes');
    Route::patch('/redes-sociais', [InfoEmpresaController::class, 'updateRedes'])->name('empresa.config.update.redes');
});

// Configuração Métodos de Pagamento
Route::get('config/pagamento', [PagamentoConfigController::class, 'index'])->name('config.pagamento');
Route::patch('config/pagamento', [PagamentoConfigController::class, 'update'])->name('config.pagamento.update');
