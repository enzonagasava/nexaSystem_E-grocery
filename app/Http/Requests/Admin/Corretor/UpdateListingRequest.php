<?php

namespace App\Http\Requests\Admin\Corretor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateListingRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() !== null;
    }

    public function rules()
    {
        // Se é JSON request, permite atualizar dados do imóvel também
        if ($this->expectsJson() || $this->wantsJson()) {
            return [
                // Dados do anúncio
                'anuncio_ativo' => ['sometimes', 'boolean'],
                'anuncio_status' => ['nullable', 'string'],
                
                // Dados básicos do imóvel
                'imovel.nome' => ['sometimes', 'string', 'max:255'],
                'imovel.descricao' => ['nullable', 'string'],
                'imovel.status' => ['sometimes', 'string'],
                'imovel.categoria' => ['sometimes', 'string'],
                'imovel.condicao' => ['sometimes', 'string'],
                'imovel.exclusividade' => ['sometimes', 'boolean'],
                
                // Valores do imóvel
                'imovel.valor_venda' => ['nullable', 'numeric'],
                'imovel.valor_locacao' => ['nullable', 'numeric'],
                'imovel.valor_condominio' => ['nullable', 'numeric'],
                'imovel.valor_iptu' => ['nullable', 'numeric'],
                'imovel.aceita_financiamento' => ['sometimes', 'boolean'],
                'imovel.aceita_permuta' => ['sometimes', 'boolean'],
                'imovel.comissao_percent' => ['nullable', 'numeric'],
                'imovel.comissao_valor' => ['nullable', 'numeric'],
                
                // Características do imóvel
                'imovel.quartos' => ['nullable', 'integer'],
                'imovel.suites' => ['nullable', 'integer'],
                'imovel.banheiros' => ['nullable', 'integer'],
                'imovel.vagas' => ['nullable', 'integer'],
                'imovel.salas' => ['nullable', 'integer'],
                'imovel.area_total' => ['nullable', 'numeric'],
                'imovel.area_construida' => ['nullable', 'numeric'],
                'imovel.area_util' => ['nullable', 'numeric'],
                'imovel.ano_construcao' => ['nullable', 'integer'],
                'imovel.mobilia' => ['nullable', 'string'],
                'imovel.itens' => ['nullable', 'array'],
                'imovel.areas_lazer' => ['nullable', 'string'],
                'imovel.varanda' => ['sometimes', 'boolean'],
                
                // Endereço do imóvel
                'imovel.cep' => ['nullable', 'string', 'max:10'],
                'imovel.estado' => ['nullable', 'string', 'max:2'],
                'imovel.cidade' => ['nullable', 'string', 'max:255'],
                'imovel.bairro' => ['nullable', 'string', 'max:255'],
                'imovel.endereco' => ['nullable', 'string', 'max:255'],
                'imovel.numero' => ['nullable', 'string', 'max:20'],
                'imovel.complemento' => ['nullable', 'string', 'max:255'],
                'imovel.referencia' => ['nullable', 'string', 'max:255'],
            ];
        }

        return [
            'anuncio_ativo' => ['sometimes', 'boolean'],
            'anuncio_status' => ['nullable', 'string'],
        ];
    }
}
