<?php

namespace App\Http\Requests\Clinica;

use Illuminate\Foundation\Http\FormRequest;

class ConsultaStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paciente_id' => ['required', 'integer', 'exists:pacientes,id'],
            'data_consulta' => ['required', 'date'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fim' => ['nullable', 'date_format:H:i', 'after:hora_inicio'],
            'tipo' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:agendada,em-andamento,realizada,cancelada'],
            'valor' => ['nullable', 'numeric', 'min:0'],
            'motivo' => ['nullable', 'string', 'max:500'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
            'diagnostico' => ['nullable', 'string', 'max:2000'],
            'prescricao' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'paciente_id.required' => 'Selecione um paciente.',
            'paciente_id.exists' => 'Paciente não encontrado.',
            'data_consulta.required' => 'A data da consulta é obrigatória.',
            'data_consulta.date' => 'Informe uma data válida.',
            'hora_inicio.required' => 'O horário de início é obrigatório.',
            'hora_inicio.date_format' => 'Informe um horário válido (HH:MM).',
            'hora_fim.date_format' => 'Informe um horário válido (HH:MM).',
            'hora_fim.after' => 'O horário de término deve ser após o início.',
            'tipo.required' => 'O tipo de consulta é obrigatório.',
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'Status inválido.',
            'valor.numeric' => 'O valor deve ser numérico.',
            'valor.min' => 'O valor não pode ser negativo.',
        ];
    }
}
