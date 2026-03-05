<?php

namespace App\Helpers;

class NumberHelper
{
    /**
     * Converte um valor monetário (ex: "R$ 1.234,56") para float (ex: 1234.56)
     */
    public static function parseMoney(string $value): float
    {
        $value = str_replace(['R$', ' ', '.'], '', $value);
        $value = str_replace(',', '.', $value);

        return (float) $value;
    }
}
