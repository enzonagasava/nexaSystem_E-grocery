<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    use HasFactory;

    protected $connection = 'tenant_content';
    protected $table = 'listings';

    protected $fillable = [
        'imovel_id',
        'anuncio_ativo',
        'anuncio_status',
        'anuncio_tipos',
    ];

    protected $casts = [
        'anuncio_ativo' => 'boolean',
        'anuncio_tipos' => 'array',
    ];

    public function imovel()
    {
        return $this->belongsTo(Imovel::class);
    }
}
