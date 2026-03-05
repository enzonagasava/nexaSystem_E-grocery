<?php

namespace App\Http\Requests\Admin\Corretor;

use Illuminate\Foundation\Http\FormRequest;

class IndexImovelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Prepare the data for validation.
     * Converte strings "true"/"false" para booleans reais,
     * pois Inertia serializa booleans como strings na URL.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('has_listing')) {
            $value = $this->input('has_listing');
            if ($value === 'true' || $value === '1') {
                $this->merge(['has_listing' => true]);
            } elseif ($value === 'false' || $value === '0') {
                $this->merge(['has_listing' => false]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Busca livre por texto (nome, descrição)
            'q' => ['nullable', 'string', 'max:255'],
            
            // Filtro por cidade
            'cidade' => ['nullable', 'string', 'max:255'],
            
            // Filtro por condição (venda, locação, etc)
            'condicao' => ['nullable', 'string', 'max:255'],
            
            // Filtro por categoria
            'categoria' => ['nullable', 'string', 'max:255'],
            
            // Filtro por status
            'status' => ['nullable', 'string', 'max:255'],
            
            // Faixa de preço (valor de venda)
            'min_valor' => ['nullable', 'numeric', 'min:0'],
            'max_valor' => ['nullable', 'numeric', 'min:0', 'gte:min_valor'],
            
            // Filtro para imóveis com ou sem anúncio
            'has_listing' => ['nullable', 'boolean'],
            // Mínimo de anúncios quando filtrando por imóveis com anúncio
            'min_listings' => ['nullable', 'integer', 'min:1'],
            
            // Filtro por quantidade de quartos (mínimo)
            'min_quartos' => ['nullable', 'integer', 'min:0'],
            
            // Filtro por quantidade de vagas (mínimo)
            'min_vagas' => ['nullable', 'integer', 'min:0'],
            
            // Ordenação
            'sort' => ['nullable', 'string', 'in:created_at,valor_venda,valor_locacao,nome'],
            'order' => ['nullable', 'string', 'in:asc,desc'],
            
            // Paginação
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'max_valor.gte' => 'O valor máximo deve ser maior ou igual ao valor mínimo.',
            'sort.in' => 'Ordenação inválida. Use: created_at, valor_venda, valor_locacao ou nome.',
            'order.in' => 'Direção de ordenação inválida. Use: asc ou desc.',
        ];
    }
}
