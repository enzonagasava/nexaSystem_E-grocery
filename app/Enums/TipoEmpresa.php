<?php

namespace App\Enums;

enum TipoEmpresa: string
{
<<<<<<< HEAD
    case ecommerce = 'CRM Ecommerce';
    case clinica = 'CRM Clínica';
    case corretor = 'CRM Imobiliário';
=======
    case Ecommerce = 'ecommerce';
    case Clinica = 'clinica';
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f

    /**
     * Retorna o label amigável para exibição.
     */
    public function label(): string
    {
        return match ($this) {
<<<<<<< HEAD
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
=======
            self::Ecommerce => 'E-commerce',
            self::Clinica => 'Clínica Médica',
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
        };
    }

    /**
     * Retorna a rota do dashboard para este tipo.
     */
    public function dashboardRoute(): string
    {
        return match ($this) {
<<<<<<< HEAD
            self::ecommerce => 'admin.ecommerce.dashboard',
            self::clinica => 'admin.clinica.dashboard',
            self::corretor => 'admin.corretor.dashboard',
=======
            self::Ecommerce => 'admin.ecommerce.dashboard',
            self::Clinica => 'admin.clinica.dashboard',
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
        };
    }

    /**
     * Retorna o prefixo de rotas para este tipo.
     */
    public function routePrefix(): string
    {
        return match ($this) {
<<<<<<< HEAD
            self::ecommerce => 'ecommerce',
            self::clinica => 'clinica',
            self::corretor => 'corretor',
        };
    }
=======
            self::Ecommerce => 'ecommerce',
            self::Clinica => 'clinica',
        };
    }

>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
    /**
     * Retorna todos os tipos disponíveis como array para selects.
     */
    public static function toSelectArray(): array
    {
        return array_map(
<<<<<<< HEAD
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
=======
            fn(self $tipo) => ['value' => $tipo->value, 'label' => $tipo->label()],
            self::cases()
        );
    }
}
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
