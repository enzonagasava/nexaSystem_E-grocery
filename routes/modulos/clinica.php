<?php

use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\ChatSettingsController;
use App\Http\Controllers\Admin\Clinica\DashboardController;
use App\Http\Controllers\Admin\Clinica\PacienteController;
use App\Http\Controllers\Admin\Clinica\ConsultaController;
use App\Http\Controllers\Admin\Clinica\ProntuarioController;
use App\Http\Controllers\Admin\Clinica\AgendamentoController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Rotas do Módulo Clínica Médica
|--------------------------------------------------------------------------
|
| Rotas específicas para empresas do tipo clínica médica.
| Middleware: jwt.cookie, auth, tipo:clinica
| Prefixo: /admin/clinica
| Nome: admin.clinica.
|
*/

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Menu (lista de itens do sidebar)
Route::get('menu', function () {
    return Inertia::render('admin/MenuIndex');
})->name('menu.index');

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

// Pacientes e Prontuários — permissão pacientes.visualizar
Route::middleware(['permissao:pacientes.visualizar'])->group(function () {
    Route::get('/pacientes/buscar', [SearchController::class, 'buscarPaciente'])->name('pacientes.buscar');
    Route::resource('pacientes', PacienteController::class);
    Route::resource('prontuarios', ProntuarioController::class);
});

// Consultas e Agendamentos — permissão agenda.visualizar
Route::middleware(['permissao:agenda.visualizar'])->group(function () {
    Route::put('/consultas/{consulta}/status', [ConsultaController::class, 'atualizarStatus'])->name('consultas.status');
    Route::resource('consultas', ConsultaController::class);
    Route::put('/agendamentos/{agendamento}/status', [AgendamentoController::class, 'atualizarStatus'])->name('agendamentos.status');
    Route::resource('agendamentos', AgendamentoController::class);
});
