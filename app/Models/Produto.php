<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;;

class Produto extends Model
{
<<<<<<< HEAD
    protected $connection = 'tenant_content';
=======
    protected $connection = 'content';
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f

    protected $fillable = [
        'user_id',
        'nome',
        'descricao',
        'estoque',
    ];

    // protected $casts = [
    //     'tamanhos' => 'array', // cast para json array
    // ];

    /**
     * Relacionamento 1:N com imagens do anúncio
     */
    public function imagens(): HasMany
    {
        return $this->hasMany(ProdutoImagem::class, 'produto_id')->orderBy('ordem');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tamanhos()
    {
        return $this->belongsToMany(Tamanho::class, 'produto_tamanho')
                    ->withPivot('preco')
                    ->select('tamanhos.id', 'tamanhos.nome');
    }
}
