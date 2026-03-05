<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RedeSocialCliente extends Model
{
    use HasFactory;

    protected $table = 'rede_social_clientes';
    
    protected $fillable = [
        'lead_id',
        'facebook',
        'instagram',
        'linkedin',
        'youtube',
        'tiktok',
        'x'
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Relacionamento com Lead/Cliente
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
    
    /**
     * Acessor para URLs completas
     */
    public function getFacebookUrlAttribute()
    {
        return $this->facebook ? "https://facebook.com/{$this->facebook}" : null;
    }
    
    public function getInstagramUrlAttribute()
    {
        return $this->instagram ? "https://instagram.com/{$this->instagram}" : null;
    }
    
    public function getLinkedinUrlAttribute()
    {
        return $this->linkedin ? "https://linkedin.com/in/{$this->linkedin}" : null;
    }
}