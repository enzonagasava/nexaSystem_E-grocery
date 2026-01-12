<?php

use App\Http\Controllers\Admin\Clinica\DashboardController;
use App\Http\Controllers\Admin\Clinica\PacienteController;
use App\Http\Controllers\Admin\Clinica\ConsultaController;
use App\Http\Controllers\Admin\Clinica\ProntuarioController;
use App\Http\Controllers\Admin\Clinica\AgendamentoController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

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

// Buscar pacientes (autocomplete)
Route::get('/pacientes/buscar', [SearchController::class, 'buscarPaciente'])->name('pacientes.buscar');

// Pacientes
Route::resource('pacientes', PacienteController::class);

// Consultas - rotas adicionais
Route::put('/consultas/{consulta}/status', [ConsultaController::class, 'atualizarStatus'])->name('consultas.status');

// Consultas
Route::resource('consultas', ConsultaController::class);

// Prontuários
Route::resource('prontuarios', ProntuarioController::class);

// Agendamentos - rotas adicionais
Route::put('/agendamentos/{agendamento}/status', [AgendamentoController::class, 'atualizarStatus'])->name('agendamentos.status');

// Agendamentos
Route::resource('agendamentos', AgendamentoController::class);
