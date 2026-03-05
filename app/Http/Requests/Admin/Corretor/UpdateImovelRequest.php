<?php

namespace App\Http\Requests\Admin\Corretor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateImovelRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // Allow partial updates for AJAX, Inertia, and JSON requests
        // e.g., when frontend sends only media files or { status: '...' }
        if ($this->expectsJson() || $this->ajax() || $this->header('X-Inertia')) {
            return [
                // Basic
                'status' => 'sometimes|required|string',
                'nome' => 'sometimes|required|string',
                'codigo' => 'sometimes|nullable|string',
                'condicao' => 'sometimes|nullable|string',
                'categoria' => 'sometimes|nullable|string',
                'exclusividade' => 'sometimes|boolean',
                'descricao' => 'sometimes|nullable|string',

                // Endereço
                'cep' => 'sometimes|nullable|string',
                'endereco' => 'sometimes|nullable|string',
                'numero' => 'sometimes|nullable|string',
                'complemento' => 'sometimes|nullable|string',
                'referencia' => 'sometimes|nullable|string',
                'bairro' => 'sometimes|nullable|string',
                'cidade' => 'sometimes|nullable|string',
                'estado' => 'sometimes|nullable|string',
                'andar' => 'sometimes|nullable|string',
                'torre' => 'sometimes|nullable|string',
                'mostrar_endereco_completo' => 'sometimes|boolean',

                // Valores
                'valor_venda' => 'sometimes|nullable|numeric',
                'valor_locacao' => 'sometimes|nullable|numeric',
                'valor_condominio' => 'sometimes|nullable|numeric',
                'valor_iptu' => 'sometimes|nullable|numeric',
                'gas' => 'sometimes|nullable|numeric',
                'luz' => 'sometimes|nullable|numeric',
                'aceita_financiamento' => 'sometimes|boolean',
                'aceita_permuta' => 'sometimes|boolean',
                'comissao_percent' => 'sometimes|nullable|numeric',
                'comissao_valor' => 'sometimes|nullable|numeric',

                // Características
                'quartos' => 'sometimes|nullable|integer',
                'suites' => 'sometimes|nullable|integer',
                'banheiros' => 'sometimes|nullable|integer',
                'vagas' => 'sometimes|nullable|integer',
                'salas' => 'sometimes|nullable|integer',
                'area_construida' => 'sometimes|nullable|numeric',
                'area_util' => 'sometimes|nullable|numeric',
                'area_total' => 'sometimes|nullable|numeric',
                'area_terreno_largura' => 'sometimes|nullable|numeric',
                'area_terreno_comprimento' => 'sometimes|nullable|numeric',
                'ano_construcao' => 'sometimes|nullable|integer',
                'mobilia' => 'sometimes|nullable|string',
                'varanda' => 'sometimes|boolean',
                'areas_lazer' => 'sometimes|nullable|string',
                'itens' => 'sometimes|nullable|string',

                // Proprietário
                'proprietario_nome' => 'sometimes|nullable|string',
                'proprietario_telefone' => 'sometimes|nullable|string',
                'proprietario_email' => 'sometimes|nullable|email',

                // Multiple media support on updates
                'imagens' => 'sometimes|array',
                'imagens.*' => 'nullable|file|mimes:png,jpg,jpeg,webp|max:5120',
                'videos' => 'sometimes|array',
                'videos.*' => 'nullable|file|mimes:mp4,webm,mov,avi|max:1048576',
                'autorizacao' => 'sometimes|nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'planta' => 'sometimes|nullable|array',
                'planta.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            ];
        }

        return (new StoreImovelRequest())->rules();
    }
}
