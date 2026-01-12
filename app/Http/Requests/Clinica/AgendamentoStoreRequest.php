<?php

namespace App\Http\Requests\Clinica;

use Illuminate\Foundation\Http\FormRequest;

class AgendamentoStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paciente_id' => ['required', 'integer', 'exists:pacientes,id'],
            'data' => ['required', 'date'],
            'hora' => ['required', 'date_format:H:i'],
            'duracao_minutos' => ['nullable', 'integer', 'min:5', 'max:480'],
            'tipo' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:pendente,confirmado,cancelado,realizado'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'paciente_id.required' => 'Selecione um paciente.',
            'paciente_id.exists' => 'Paciente não encontrado.',
            'data.required' => 'A data do agendamento é obrigatória.',
            'data.date' => 'Informe uma data válida.',
            'hora.required' => 'O horário é obrigatório.',
            'hora.date_format' => 'Informe um horário válido (HH:MM).',
            'duracao_minutos.integer' => 'A duração deve ser um número inteiro.',
            'duracao_minutos.min' => 'A duração mínima é de 5 minutos.',
            'duracao_minutos.max' => 'A duração máxima é de 480 minutos (8 horas).',
            'tipo.required' => 'O tipo de agendamento é obrigatório.',
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'Status inválido.',
        ];
    }
}
