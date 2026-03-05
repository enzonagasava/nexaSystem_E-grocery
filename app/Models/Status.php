<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Status extends Model

{
    use SoftDeletes;
    protected $connection = 'tenant_content';


    protected $table = 'status';

    protected $fillable = [
        'id',
        'nome',
        'descricao',
        'ordem'
    ];

            
    function clienteImobiliaria()
    {
        return $this->belongsToMany(clienteImobiliaria::class, 'status');
    }

    public function quadro()
    {
        return $this->hasOneThrough(
            KanbanQuadro::class,
            KanbanColuna::class,
            'status_id',
            'id',
            'id',
            'kanban_quadro_id'
        );
    }

    function kanbanColuna(): BelongsTo
    {
        return $this->BelongsTo(KanbanColuna::class, 'id', 'status_id');
    }
        
}
