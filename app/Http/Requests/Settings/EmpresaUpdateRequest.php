<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Empresa;
use Illuminate\Validation\Rule;

class EmpresaUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // true se o usuário autenticado puder editar
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'lowercase', 'email',
            Rule::unique(Empresa::class, 'email')->ignore(1),],
            'numero_wpp' => ['nullable', 'string', 'max:225'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'cnpj' => ['nullable', 'string', 'max:18'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'cep' => ['nullable', 'string', 'max:10'],
            'numero_endereco' => ['nullable', 'string', 'max:10'],
            'municipio' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'max:2'],
        ];
    }
}
