<?php

namespace App\Enums;

enum TipoEmpresa: string
{
    case ecommerce = 'CRM Ecommerce';
    case clinica = 'CRM Clínica';
    case corretor = 'CRM Imobiliário';

    /**
     * Retorna o label amigável para exibição.
     */
    public function label(): string
    {
        return match ($this) {
            self::ecommerce => 'E-commerce',
            self::clinica => 'Clínica Médica',
            self::corretor => 'Corretor Imobiliário',
        };
    }

    /**
     * Retorna o slug/sigla simplificada (para uso em rotas).
     */
    public function slug(): string
    {
        return match ($this) {
            self::ecommerce => 'ecommerce',
            self::clinica => 'clinica',
            self::corretor => 'corretor',
        };
    }

    /**
     * Retorna a rota do dashboard para este tipo.
     */
    public function dashboardRoute(): string
    {
        return match ($this) {
            self::ecommerce => 'admin.ecommerce.dashboard',
            self::clinica => 'admin.clinica.dashboard',
            self::corretor => 'admin.corretor.dashboard',
        };
    }

    /**
     * Retorna o prefixo de rotas para este tipo.
     */
    public function routePrefix(): string
    {
        return match ($this) {
            self::ecommerce => 'ecommerce',
            self::clinica => 'clinica',
            self::corretor => 'corretor',
        };
    }
    /**
     * Retorna todos os tipos disponíveis como array para selects.
     */
    public static function toSelectArray(): array
    {
        return array_map(
            fn(self $tipo) => [
                'value' => $tipo->value, 
                'label' => $tipo->label(),
                'slug' => $tipo->slug()
            ],
            self::cases()
        );
    }

        public static function fromSlug(string $slug): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->slug() === $slug) {
                return $case;
            }
        }
        return null;
    }

    /**
     * Retorna array de slugs disponíveis.
     */
    public static function slugs(): array
    {
        return array_map(fn(self $tipo) => $tipo->slug(), self::cases());
    }

    /**
     * Retorna array de valores disponíveis.
     */
    public static function values(): array
    {
        return array_map(fn(self $tipo) => $tipo->value, self::cases());
    }
}