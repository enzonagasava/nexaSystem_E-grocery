<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdutoTamanho extends Model
{
<<<<<<< HEAD
    protected $connection = 'tenant_content';

=======
    protected $connection = 'content';
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f

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
