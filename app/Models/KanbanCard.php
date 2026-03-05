<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KanbanCard extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant_content';

    protected $table = 'kanban_cards';

    protected $fillable = [
        'kanban_coluna_id',
        'entidade_id',
        'entidade_type',
        'user_id',
        'titulo',
        'subtitulo',
        'descricao',
        'dados_cache',
        'etiquetas',
        'anexos',
        'checklists',
        'data_entrada',
        'data_limite',
        'data_conclusao',
        'ordem',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'dados_cache' => 'array',
        'etiquetas' => 'array',
        'anexos' => 'array',
        'checklists' => 'array',
        'data_entrada' => 'datetime',
        'data_limite' => 'datetime',
        'data_conclusao' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the column that owns the card.
     */
    public function coluna(): BelongsTo
    {
        return $this->belongsTo(KanbanColuna::class, 'kanban_coluna_id');
    }

    /**
     * Get the user that owns the card.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the parent entity (lead, contato, etc).
     */
    public function entidade()
    {
        return $this->morphTo('entidade', 'entidade_type', 'entidade_id');
    }
}