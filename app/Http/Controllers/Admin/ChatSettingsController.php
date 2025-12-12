<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracaoIa;
use App\Models\RespostaRapida;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChatSettingsController extends Controller
{
    public function index()
    {
        $config = ConfiguracaoIa::getConfig();
        $respostasRapidas = RespostaRapida::orderBy('atalho')->get();

        return Inertia::render('admin/chat/Settings', [
            'config' => $config,
            'respostasRapidas' => $respostasRapidas
        ]);
    }

    public function updateConfig(Request $request)
    {
        $validated = $request->validate([
            'bot_ativo' => 'required|boolean',
            'tom_voz' => 'required|in:amigavel,profissional',
            'mensagem_boas_vindas' => 'nullable|string',
            'mensagem_fora_horario' => 'nullable|string',
            'timer_ausencia' => 'required|integer|min:0',
            'bloquear_bot' => 'required|boolean',
            'bloqueio_ate' => 'nullable|date'
        ]);

        $config = ConfiguracaoIa::getConfig();
        $config->update($validated);

        return back()->with('success', 'Configurações atualizadas com sucesso!');
    }

    public function storeRespostaRapida(Request $request)
    {
        $validated = $request->validate([
            'atalho' => 'required|string|max:50|unique:respostas_rapidas,atalho',
            'mensagem' => 'required|string',
            'ativo' => 'boolean'
        ]);

        RespostaRapida::create($validated);

        return back()->with('success', 'Resposta rápida criada com sucesso!');
    }

    public function updateRespostaRapida(Request $request, RespostaRapida $respostaRapida)
    {
        $validated = $request->validate([
            'atalho' => 'required|string|max:50|unique:respostas_rapidas,atalho,' . $respostaRapida->id,
            'mensagem' => 'required|string',
            'ativo' => 'boolean'
        ]);

        $respostaRapida->update($validated);

        return back()->with('success', 'Resposta rápida atualizada com sucesso!');
    }

    public function destroyRespostaRapida(RespostaRapida $respostaRapida)
    {
        $respostaRapida->delete();

        return back()->with('success', 'Resposta rápida excluída com sucesso!');
    }
}
