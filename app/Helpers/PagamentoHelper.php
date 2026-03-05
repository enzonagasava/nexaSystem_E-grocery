<?php

namespace App\Helpers;

use App\Models\IntegracaoPagamento;

class PagamentoHelper
{
    public static function getKeys($empresa_id)
    {
        return IntegracaoPagamento::where('empresa_id', $empresa_id)->first();
    }
}

