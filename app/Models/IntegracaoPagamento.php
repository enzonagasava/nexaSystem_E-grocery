<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class IntegracaoPagamento extends BaseModel
{
    protected $connection = 'credentials';

    protected $table = 'integracoes_pagamento';

    protected $fillable = [
        'public_key_encrypted',
        'access_key_encrypted',
    ];

    public function getPublicKeyAttribute(): ?string
    {
        if (!$this->public_key_encrypted) {
            return null;
        }

        try {
            return Crypt::decryptString($this->public_key_encrypted);
        } catch (DecryptException $e) {
            return null;
        }
    }

    public function getAccessTokenAttribute(): ?string
    {
        if (!$this->access_key_encrypted) {
            return null;
        }

        try {
            return Crypt::decryptString($this->access_key_encrypted);
        } catch (DecryptException $e) {
            return null;
        }
    }
}
