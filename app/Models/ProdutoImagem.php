<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProdutoImagem extends Model
{
    protected $connection = 'tenant_content';


    protected $table = 'produto_imagens';

    protected $fillable = [
        'produto_id',
        'user_id',
        'imagem_path',
        'ordem',
    ];

    /* cria um campo virtual e o proprio eloquent relaciona com a função logo abaixo
    */
    protected $appends = ['imagem_url'];
    public function getImagemUrlAttribute()
    {
        if (!$this->imagem_path) {
            return null;
        }

        return asset('storage/' . $this->imagem_path);
    }

    /**
     * Relacionamento inverso com o anúncio
     */
    public function anuncio(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

    /**
     * Relacionamento com o usuário dono da imagem
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
