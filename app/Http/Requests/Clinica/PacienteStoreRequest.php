<?php

namespace App\Http\Requests\Clinica;

use Illuminate\Foundation\Http\FormRequest;

class PacienteStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $pacienteId = $this->route('paciente');

        return [
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['nullable', 'string', 'max:14', $pacienteId ? "unique:pacientes,cpf,{$pacienteId}" : 'unique:pacientes,cpf'],
            'data_nascimento' => ['nullable', 'date'],
            'sexo' => ['nullable', 'in:masculino,feminino,outro'],
            'telefone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'cep' => ['nullable', 'string', 'max:10'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'numero_endereco' => ['nullable', 'string', 'max:20'],
            'bairro' => ['nullable', 'string', 'max:100'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'max:2'],
            'convenio' => ['nullable', 'string', 'max:100'],
            'numero_convenio' => ['nullable', 'string', 'max:50'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do paciente é obrigatório.',
            'nome.max' => 'O nome não pode ter mais de 255 caracteres.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'telefone.required' => 'O telefone é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'data_nascimento.date' => 'Informe uma data de nascimento válida.',
            'sexo.in' => 'Selecione um sexo válido.',
        ];
    }
}
