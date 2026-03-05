<?php

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;

class PedidosStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Se tiver auth, use policy; caso contrário, true
        return true;
    }

    public function rules(): array
    {
        return [
            // --- Cliente ---
            'clienteSelecionado.id' => ['required', 'integer', 'exists:tenant_content.clientes,id'],
            'clienteSelecionado.nome' => ['required', 'string', 'max:255'],
            'clienteSelecionado.email' => ['nullable', 'email', 'max:255'],
            'clienteSelecionado.numero' => ['nullable', 'string', 'max:20'],

            // --- Pedido ---
            'id' => ['nullable', 'integer'],
            'data' => ['required', 'date'],
            'status' => ['required', 'string', 'max:30'],
            'valorTotal' => ['required', 'numeric'],
            'endereco' => ['required', 'string', 'max:225'],
            'plataformaSelecionada' => ['nullable', 'integer'],
            'cod_pedido' => ['nullable', 'string'],
            'valorTotal' => ['nullable', 'numeric'],

            // --- Produtos ---
            'produtos' => ['required', 'array', 'min:1'],
            'produtos.*.id' => ['required', 'integer', 'exists:tenant_content.produtos,id'],
            'produtos.*.quantidade' => ['required', 'numeric', 'min:1'],
            'produtos.*.valor' => ['required', 'numeric', 'min:0'],

            // --- Extras (opcional) ---
            'observacoes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'clienteSelecionado.id.required' => 'Selecione um cliente válido.',
            'produtos.required' => 'Adicione ao menos um produto.',
            'produtos.*.id.exists' => 'Um dos produtos selecionados não existe.',
            'status.in' => 'Status inválido. Use "Em Andamento", "A Caminho", "Finalizado".',
            'endereco' => 'endereço inválido'
        ];
    }

    protected function prepareForValidation()
    {
        // Caso o frontend envie campos dentro de objetos, podemos normalizar aqui
        $this->merge([
            'cliente_id' => data_get($this->clienteSelecionado, 'id'),
        ]);
    }
}
