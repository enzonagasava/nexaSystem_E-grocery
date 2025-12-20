<?php

namespace App\Enums;

enum TipoEmpresa: string
{
    case Ecommerce = 'ecommerce';
    case Clinica = 'clinica';

    /**
     * Retorna o label amigável para exibição.
     */
    public function label(): string
    {
        return match ($this) {
            self::Ecommerce => 'E-commerce',
            self::Clinica => 'Clínica Médica',
        };
    }

    /**
     * Retorna a rota do dashboard para este tipo.
     */
    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Ecommerce => 'admin.ecommerce.dashboard',
            self::Clinica => 'admin.clinica.dashboard',
        };
    }

    /**
     * Retorna o prefixo de rotas para este tipo.
     */
    public function routePrefix(): string
    {
        return match ($this) {
            self::Ecommerce => 'ecommerce',
            self::Clinica => 'clinica',
        };
    }

    /**
     * Retorna todos os tipos disponíveis como array para selects.
     */
    public static function toSelectArray(): array
    {
        return array_map(
            fn(self $tipo) => ['value' => $tipo->value, 'label' => $tipo->label()],
            self::cases()
        );
    }
}
