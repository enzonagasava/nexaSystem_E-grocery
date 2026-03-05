<?php

namespace App\Http\Requests\Admin\Corretor;

use Illuminate\Foundation\Http\FormRequest;

class StoreImovelRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // keep imovel nome optional (we store proprietário nome separately)
            'nome' => 'nullable|string|max:255',
            // require proprietário nome — will be persisted in imovel_autorizacoes
            'proprietario_nome' => 'required|string|max:255',
            'codigo' => 'nullable|string',
            'status' => 'required|string',
            'modalidade' => 'required|string',
            'condicao' => 'required|string',
            'categoria' => 'required|string',
            'exclusividade' => 'nullable|boolean',
            'cep' => 'nullable|string',
            'endereco' => 'nullable|string',
            'numero' => 'nullable|string',
            'bairro' => 'nullable|string',
            'cidade' => 'nullable|string',
            'estado' => 'nullable|string',
            'complemento' => 'nullable|string',
            'torre' => 'nullable|string',
            'andar' => 'nullable|string',
            'dormitorios' => 'nullable|integer',
            'suites' => 'nullable|integer',
            'banheiros' => 'nullable|integer',
            'vagas' => 'nullable|integer',
            'area_construida' => 'nullable|numeric',
            'area_terreno_largura' => 'nullable|numeric',
            'area_terreno_comprimento' => 'nullable|numeric',
            'area_util' => 'nullable|numeric',
            'salas' => 'nullable|integer',
            'mobilia' => 'nullable|string',
            'varanda' => 'nullable|boolean',
            'areas_lazer' => 'nullable|string',
            'descricao' => 'nullable|string',
            'itens' => 'nullable|string',
            'valor_venda' => 'nullable|numeric',
            'valor_locacao' => 'nullable|numeric',
            'valor_condominio' => 'nullable|numeric',
            'valor_iptu' => 'nullable|numeric',
            'gas' => 'nullable|string',
            'luz' => 'nullable|string',
            'aceita_financiamento' => 'nullable|boolean',
            'aceita_permuta' => 'nullable|boolean',
            'comissao_percent' => 'nullable|numeric',
            'mostrar_endereco_completo' => 'nullable|boolean',
            'ano_construcao' => 'nullable|integer',
            'proprietario_telefone' => 'nullable|string',
            'proprietario_email' => 'nullable|email',
            'proprietario_documento' => 'nullable|string',
            // Multiple media support on creation
            'imagens' => 'nullable|array',
            'imagens.*' => 'nullable|file|mimes:png,jpg,jpeg,webp|max:5120',
            'autorizacao' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'planta' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'video' => 'nullable|file|mimes:mp4,webm,mov,avi|max:1048576',
        ];
    }
}
