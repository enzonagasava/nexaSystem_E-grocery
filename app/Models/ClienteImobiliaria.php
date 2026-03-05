<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClienteImobiliaria extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant_content';

    protected $table = 'cliente_imobiliaria';

    protected $fillable = [
        'nome_completo',
        'email',
        'contatos',
        'genero',
        'data_nascimento',
        'redes_social_id',
        'cpf',
        'rg',
        'cnh',
        'renda_mensal',
        'renda_familiar',
        'status_cliente',
        'status_id',
        'tipo_relação',
        'ultimo_contato',
        'preferencias_imoveis',         
        'observacoes',
        'cep',
        'rua',
        'bairro',
        'cidade',
        'estado',
        'complemento',
        'numero',
        'banco_nome',
        'banco_codigo',
        'agencia',
        'conta',
        'conta_tipo',
        'pix',
        'pix_tipo',
        'corretor_id',
        'imovel_id',
        'adicionar_rodizio'
    ];

    protected $casts = [
        'contatos' => 'array',
        'redes_sociais' => 'array',
        'data_nascimento' => 'date',
        'adicionar_rodizio' => 'boolean',
        'numero' => 'integer',
        'is_contato' => 'boolean'
    ];

    /**
     * Relacionamento com o corretor
     */
    public function corretor()
    {
        return $this->belongsTo(User::class, 'corretor_id');
    }

    public function imovel()
    {
        return $this->belongsTo(Imovel::class, 'imovel_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function redeSocial()
    {
        return $this->hasOne(RedeSocialCliente::class, 'redes_social_id');
    }

    /**
     * Scope para leads ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('status', 'ativo');
    }

    /**
     * Scope para leads no rodízio
     */
    public function scopeNoRodizio($query)
    {
        return $query->where('adicionar_rodizio', true)
                     ->where('status', 'ativo')
                     ->whereNull('corretor_id');
    }

    /**
     * Inativar lead (soft delete)
     */
    public function inativar()
    {
        $this->status = 'inativo';
        $this->save();
        $this->delete();
    }

    /**
     * Ativar lead
     */
    public function ativar()
    {
        $this->status = 'ativo';
        $this->save();
        $this->restore();
    }

    /**
     * Adicionar novo contato
     */
    public function adicionarContato($numero, $tipo = 'celular')
    {
        $contatos = $this->contatos ?? [];
        $contatos[] = [
            'numero' => $numero,
            'tipo' => $tipo,
            'principal' => empty($contatos) // Se for o primeiro, marca como principal
        ];
        
        $this->contatos = $contatos;
        $this->save();
    }

    /**
     * Adicionar rede social
     */
    public function adicionarRedeSocial($plataforma, $url)
    {
        $redes = $this->redes_sociais ?? [];
        $redes[] = [
            'plataforma' => $plataforma,
            'url' => $url
        ];
        
        $this->redes_sociais = $redes;
        $this->save();
    }
}