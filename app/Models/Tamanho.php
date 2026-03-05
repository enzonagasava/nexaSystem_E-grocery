<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tamanho extends Model
{
<<<<<<< HEAD
    protected $connection = 'tenant_content';

=======
    protected $connection = 'content';
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f

    protected $table = 'tamanhos';

    protected $fillable = [
        'nome',
    ];
        
    function produtos()
    {
        return $this->belongsToMany(Produto::class, 'produto_tamanho')->withPivot('preco');
    }
}
