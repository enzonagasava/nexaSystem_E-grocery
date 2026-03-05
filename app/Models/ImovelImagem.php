<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImovelImagem extends Model
{
    protected $connection = 'tenant_content';
    protected $table = 'imovel_imagens';

    protected $fillable = [
        'imovel_id','user_id','imagem_path','ordem','original_name','mime_type','size','uploaded_at'
    ];

    protected $appends = ['imagem_url'];

    public function getImagemUrlAttribute()
    {
        return asset('storage/' . $this->imagem_path);
    }

    public function imovel()
    {
        return $this->belongsTo(Imovel::class, 'imovel_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
