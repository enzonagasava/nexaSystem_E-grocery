<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\App\DashboardController as ClienteDashboardController;
use App\Http\Controllers\Admin\ProdutoController;
use App\Http\Controllers\Admin\PedidoController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\ChatSettingsController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::post('/dashboardRota', [AdminDashboardController::class, 'dashboardRota'])->middleware('jwt.cookie', 'auth')->name('dashboardRota');

Route::middleware(['jwt.cookie','auth','cliente'])->prefix('cliente')->name('cliente.')->group(function () {
    Route::get('/dashboard', [ClienteDashboardController::class, 'index'])->name('dashboard');
    // rotas cliente...
});

/*
|--------------------------------------------------------------------------
| Rotas Modulares por Tipo de Empresa
|--------------------------------------------------------------------------
|
| Cada tipo de empresa (e-commerce, clínica, etc.) tem seu próprio conjunto
| de rotas protegidas pelo middleware 'tipo:X'.
|
*/

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

<<<<<<< HEAD
// Rotas do módulo Corretor (Imobiliário)
Route::middleware(['jwt.cookie', 'auth', 'tipo:corretor'])
    ->prefix('admin/corretor')
    ->name('admin.corretor.')
    ->group(base_path('routes/modulos/corretor.php'));
=======
        //Produtos Configurações
        Route::get('produtos/config', [ProdutoController::class, 'index'], function () {
            return Inertia::render('admin/produtosConfig/ProdutosConfig');
        })->name('produtos.config');
        Route::get('produtos/create-produto', [ProdutoController::class, 'create'], function () {
            return Inertia::render('admin/produtosConfig/AdicionarProduto');
        })->name('produtos.create');
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


    });

    Route::middleware(['jwt.cookie', 'auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        //Rota Canlender
        Route::get('calendar', [\App\Http\Controllers\Admin\CalendarController::class, 'index'])->name('calendar.index');
        Route::get('calendar/events', [\App\Http\Controllers\Admin\CalendarController::class, 'events'])->name('calendar.events');
        Route::post('calendar/events', [\App\Http\Controllers\Admin\CalendarController::class, 'store'])->name('calendar.store');
        Route::put('calendar/events/{id}', [\App\Http\Controllers\Admin\CalendarController::class, 'update'])->name('calendar.update');
        Route::delete('calendar/events/{id}', [\App\Http\Controllers\Admin\CalendarController::class, 'destroy'])->name('calendar.destroy');
        // Calendar settings
        Route::get('calendar/settings', [\App\Http\Controllers\Admin\CalendarSettingsController::class, 'index'])->name('calendar.settings');
        Route::get('calendar/settings/data', [\App\Http\Controllers\Admin\CalendarSettingsController::class, 'data'])->name('calendar.settings.data');
        Route::put('calendar/settings', [\App\Http\Controllers\Admin\CalendarSettingsController::class, 'update'])->name('calendar.settings.update');
        // Google Calendar OAuth (optional)
        Route::get('calendar/auth', [\App\Http\Controllers\Admin\GoogleCalendarAuthController::class, 'redirect'])->name('calendar.auth');
        Route::get('calendar/callback', [\App\Http\Controllers\Admin\GoogleCalendarAuthController::class, 'callback'])->name('calendar.callback');
        Route::post('calendar/disconnect', [\App\Http\Controllers\Admin\GoogleCalendarAuthController::class, 'disconnect'])->name('calendar.disconnect');

        //Rota Chat
        Route::get('/chat', [ChatController::class, 'index'])->name('chat');
        Route::get('/chat/conversations', [ChatController::class, 'getConversations'])->name('chat.conversations');
        Route::get('/chat/messages', [ChatController::class, 'getMessages'])->name('chat.messages');
        Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');
        Route::post('/chat/mark-read', [ChatController::class, 'markAsRead'])->name('chat.markRead');

        //Rota Chat Settings
        Route::get('/chat/settings', [ChatSettingsController::class, 'index'])->name('chat.settings');
        Route::put('/chat/settings/config', [ChatSettingsController::class, 'updateConfig'])->name('chat.settings.config');
        Route::post('/chat/settings/respostas-rapidas', [ChatSettingsController::class, 'storeRespostaRapida'])->name('chat.settings.respostas.store');
        Route::put('/chat/settings/respostas-rapidas/{respostaRapida}', [ChatSettingsController::class, 'updateRespostaRapida'])->name('chat.settings.respostas.update');
        Route::delete('/chat/settings/respostas-rapidas/{respostaRapida}', [ChatSettingsController::class, 'destroyRespostaRapida'])->name('chat.settings.respostas.destroy');

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



    });

    Route::middleware(['jwt.cookie','auth'])->prefix('cliente')->name('cliente.')->group(function () {
        Route::get('/dashboard', [ClienteDashboardController::class, 'index'])->name('dashboard');
        // rotas cliente...
    });

    /*
    |--------------------------------------------------------------------------
    | Rotas Modulares por Tipo de Empresa
    |--------------------------------------------------------------------------
    |
    | Cada tipo de empresa (e-commerce, clínica, etc.) tem seu próprio conjunto
    | de rotas protegidas pelo middleware 'tipo:X'.
    |
    */

    // Rotas do módulo E-commerce
    Route::middleware(['jwt.cookie', 'auth'])
        ->prefix('admin/ecommerce')
        ->name('admin.ecommerce.')
        ->group(base_path('routes/modulos/ecommerce.php'));

    // Rotas do módulo Clínica Médica
    Route::middleware(['jwt.cookie', 'auth'])
        ->prefix('admin/clinica')
        ->name('admin.clinica.')
        ->group(base_path('routes/modulos/clinica.php'));
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
