<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdutoTamanho extends Model
{
    protected $connection = 'tenant_content';


    protected $table = 'produto_tamanho';

    protected $fillable = [
        'produto_id',
        'tamanho_id',
        'preco',
    ];

    public function produtos()
    {
        return $this->belongsToMany(Produto::class)->withPivot('preco');
    }

    public function tamanho()
    {
        return $this->belongsTo(Tamanho::class);
    }

}
