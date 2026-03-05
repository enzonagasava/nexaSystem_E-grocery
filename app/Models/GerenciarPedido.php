<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\BaseModel;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Plataforma;


class GerenciarPedido extends BaseModel
{        
    protected $connection = 'tenant_content';

    protected $table = 'gerenciar_pedidos';

    protected $fillable = [
        'cliente_id',
        'cod_pedido',
        'valor',
        'endereco',
        'status',
        'plataforma_id'
    ];

    protected $appends = ['created_at_formatted'];

    public function pedidos()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function CodPedidos()
    {
        return $this->hasMany(Pedido::class, 'cod_pedido', 'cod_pedido');
    }

        public function produtos()
    {
        return $this->hasManyThrough(
            Produto::class,
            Pedido::class,
            'cod_pedido',
            'id',
            'cod_pedido',
            'produto_id'
        );
    }

    public function plataforma()
    {
        return $this->belongsTo(Plataforma::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
