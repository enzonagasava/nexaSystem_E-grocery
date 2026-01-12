<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $cart = $request->session()->get('cart', []);
        $cartQuantity = array_sum(array_column($cart, 'quantidade'));

        $user = $request->user();
        $userData = null;
        $empresaData = null;

        if ($user) {
            // Carregar empresa se não carregada
            if (!$user->relationLoaded('empresa')) {
                $user->load('empresa');
            }

            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'numero' => $user->numero,
                'cargo_id' => $user->cargo_id,
                'empresa_id' => $user->empresa_id,
                'tipo_empresa' => $user->empresa?->tipo?->value,
            ];

            if ($user->empresa) {
                $empresaData = [
                    'id' => $user->empresa->id,
                    'nome' => $user->empresa->nome,
                    'tipo' => $user->empresa->tipo?->value,
                    'tipo_label' => $user->empresa->tipo?->label(),
                ];
            }
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $userData,
                'empresa' => $empresaData,
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'cartQuantity' => $cartQuantity,
            'cartItems' => array_values($cart),
        ];
    }
}
