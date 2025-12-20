<?php

namespace App\Services;

use App\Models\IntegracaoPagamento;

class MercadoPagoService
{
    protected IntegracaoPagamento $integracao;

    public function __construct(IntegracaoPagamento $integracao)
    {
        $this->integracao = $integracao;
    }

    public function publicKey(): string
    {
        if (!$this->integracao->public_key) {
            throw new \Exception('Public key do Mercado Pago não configurada');
        }

        return $this->integracao->public_key;
    }

    public function accessToken(): string
    {
        if (!$this->integracao->access_token) {
            throw new \Exception('Access token do Mercado Pago não configurado');
        }

        return $this->integracao->access_token;
    }
}
