<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\IntegracaoPagamento;
use App\Http\Requests\Settings\IntegracaoPagamentoUpdateRequest;
use Illuminate\Support\Facades\Crypt;

class PagamentoConfigController extends Controller
{

    public function rules()
    {
        return [
            'public_key' => ['nullable', 'string'],
            'access_key' => ['nullable', 'string'],
        ];
    }

    public function index()
    {
        $MetodoPagamento = IntegracaoPagamento::firstOrFail();

        return inertia('admin/configuracoes/PagamentoConfig', [
            'MetodoPagamento' => $MetodoPagamento
        ]);
    }

    public function update(IntegracaoPagamentoUpdateRequest $request)
    {
        $metodo = IntegracaoPagamento::firstOrFail();

        $data = [];

        if ($request->filled('public_key')) {
            $data['public_key_encrypted'] =
                Crypt::encryptString($request->public_key);
        }

        if ($request->filled('access_key')) {
            $data['access_key_encrypted'] =
                Crypt::encryptString($request->access_key);
        }

        if ($data) {
            $metodo->update($data);
        }

        return back()->with('success', 'Credenciais atualizadas com sucesso.');
    }
}
