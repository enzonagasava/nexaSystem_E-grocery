<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\BaseModel;

class Pedido extends BaseModel
{
    protected $connection = 'tenant_content';

    protected $table = 'pedidos';

    protected $fillable = [
        'produto_id',
        'quantidade',
        'cod_pedido',
        'valor_pedido',
    ];

    public function gerenciar()
    {
        return $this->belongsTo(GerenciarPedido::class, 'cod_pedido', 'cod_pedido');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

}
