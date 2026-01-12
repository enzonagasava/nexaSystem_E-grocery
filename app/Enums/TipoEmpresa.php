<?php

namespace App\Enums;

enum TipoEmpresa: string
{
    case Ecommerce = 'ecommerce';
    case Clinica = 'clinica';
    case Corretor = 'corretor';

    /**
     * Retorna o label amigável para exibição.
     */
    public function label(): string
    {
        return match ($this) {
            self::Ecommerce => 'E-commerce',
            self::Clinica => 'Clínica Médica',
            self::Corretor => 'Corretor de Imóveis',
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
            self::Corretor => 'admin.corretor.dashboard',
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
            self::Corretor => 'corretor',
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
