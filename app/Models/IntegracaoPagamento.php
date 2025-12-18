<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\BaseModel;

class IntegracaoPagamento extends BaseModel
{
    protected $connection = 'credentials';

    protected $table = 'integracoes_pagamento';

    protected $fillable = [
        'public_key_encrypted',
        'access_key_encrypted'
    ];


    public function getClientSecretAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function getWebhookSecretAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

}
