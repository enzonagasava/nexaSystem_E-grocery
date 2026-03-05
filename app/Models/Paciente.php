<?php

namespace App\Models;

use App\Models\BaseModel;

class Paciente extends BaseModel
{
<<<<<<< HEAD
    protected $connection = 'tenant_content';
=======
    protected $connection = 'content';
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f

    protected $table = 'pacientes';

    protected $fillable = [
        'empresa_id',
        'nome',
        'cpf',
        'data_nascimento',
        'sexo',
        'telefone',
        'email',
        'cep',
        'endereco',
        'numero_endereco',
        'bairro',
        'cidade',
        'estado',
        'convenio',
        'numero_convenio',
        'observacoes',
    ];

    protected $appends = ['created_at_formatted', 'endereco_completo', 'idade'];

    protected $casts = [
        'data_nascimento' => 'date',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function consultas()
    {
        return $this->hasMany(Consulta::class);
    }

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class);
    }

    public function getEnderecoCompletoAttribute(): string
    {
        $partes = array_filter([
            $this->endereco,
            $this->numero_endereco ? "nº {$this->numero_endereco}" : null,
            $this->bairro,
            $this->cidade,
            $this->estado,
        ]);

        return implode(', ', $partes) ?: '-';
    }

    public function getIdadeAttribute(): ?int
    {
        if (!$this->data_nascimento) {
            return null;
        }

        return $this->data_nascimento->age;
    }
}
