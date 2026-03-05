<?php

namespace App\Http\Requests\Admin\Corretor;

use Illuminate\Foundation\Http\FormRequest;

class StoreListingRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() !== null;
    }

    public function rules()
    {
        $rules = [
            'mode' => ['required', 'string', 'in:existing,new'],
            'anuncio_ativo' => ['sometimes', 'boolean'],
            'anuncio_status' => ['nullable', 'string'],
        ];

        if ($this->input('mode') === 'new') {
            // Regras para criação de novo imóvel inline
            $rules = array_merge($rules, [
                'imovel' => ['required', 'array'],
                'imovel.nome' => ['nullable', 'string', 'max:255'],
                'imovel.proprietario_nome' => ['required', 'string', 'max:255'],
                'imovel.codigo' => ['nullable', 'string'],
                'imovel.status' => ['required', 'string'],
                'imovel.modalidade' => ['required', 'string'],
                'imovel.condicao' => ['required', 'string'],
                'imovel.categoria' => ['required', 'string'],
                'imovel.exclusividade' => ['nullable', 'boolean'],
                'imovel.descricao' => ['nullable', 'string'],
                // Endereço
                'imovel.cep' => ['nullable', 'string'],
                'imovel.endereco' => ['nullable', 'string'],
                'imovel.numero' => ['nullable', 'string'],
                'imovel.bairro' => ['nullable', 'string'],
                'imovel.cidade' => ['nullable', 'string'],
                'imovel.estado' => ['nullable', 'string'],
                'imovel.complemento' => ['nullable', 'string'],
                'imovel.referencia' => ['nullable', 'string'],
                'imovel.mostrar_endereco_completo' => ['nullable', 'boolean'],
                // Valores
                'imovel.valor_venda' => ['nullable', 'numeric'],
                'imovel.valor_locacao' => ['nullable', 'numeric'],
                'imovel.valor_condominio' => ['nullable', 'numeric'],
                'imovel.valor_iptu' => ['nullable', 'numeric'],
                'imovel.aceita_financiamento' => ['nullable', 'boolean'],
                'imovel.aceita_permuta' => ['nullable', 'boolean'],
                'imovel.comissao_percent' => ['nullable', 'numeric'],
                'imovel.comissao_valor' => ['nullable', 'numeric'],
                // Características
                'imovel.area_total' => ['nullable', 'numeric'],
                'imovel.area_construida' => ['nullable', 'numeric'],
                'imovel.area_util' => ['nullable', 'numeric'],
                'imovel.quartos' => ['nullable', 'integer'],
                'imovel.suites' => ['nullable', 'integer'],
                'imovel.banheiros' => ['nullable', 'integer'],
                'imovel.vagas' => ['nullable', 'integer'],
                'imovel.salas' => ['nullable', 'integer'],
                'imovel.andar' => ['nullable', 'integer'],
                'imovel.ano_construcao' => ['nullable', 'integer'],
                'imovel.mobilia' => ['nullable', 'string'],
                'imovel.varanda' => ['nullable', 'boolean'],
                'imovel.areas_lazer' => ['nullable', 'string'],
                'imovel.itens' => ['nullable', 'string'],
                // Proprietário
                'imovel.proprietario_telefone' => ['nullable', 'string'],
                'imovel.proprietario_email' => ['nullable', 'email'],
                'imovel.proprietario_documento' => ['nullable', 'string'],
            ]);
        } else {
            // Vincular imóvel existente
            $rules['imovel_id'] = ['required', 'integer', 'exists:tenant_content.imoveis,id'];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'imovel_id.required' => 'Selecione um imóvel para vincular ao anúncio.',
            'imovel_id.exists' => 'O imóvel selecionado não foi encontrado.',
            'imovel.proprietario_nome.required' => 'O nome do proprietário é obrigatório.',
            'imovel.status.required' => 'O status do imóvel é obrigatório.',
            'imovel.modalidade.required' => 'A modalidade é obrigatória.',
            'imovel.condicao.required' => 'A condição do imóvel é obrigatória.',
            'imovel.categoria.required' => 'A categoria do imóvel é obrigatória.',
        ];
    }
}
