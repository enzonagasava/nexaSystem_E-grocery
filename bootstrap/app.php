<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\HandleExceptions;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Foundation\Bootstrap\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (\Illuminate\Foundation\Configuration\Middleware $middleware) {

        $middleware->alias([
            'jwt.cookie' => \App\Http\Middleware\JwtCookieMiddleware::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'gestor' => \App\Http\Middleware\GestorMiddleware::class,
            'cliente' => \App\Http\Middleware\ClienteMiddleware::class,
            'tipo' => \App\Http\Middleware\CheckTipoEmpresa::class,
<<<<<<< HEAD
            'permissao' => \App\Http\Middleware\VerificarPermissao::class,
            'api.token' => \App\Http\Middleware\NexaVerifyApiToken::class,
=======
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
        ]);       
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            \Illuminate\Session\Middleware\StartSession::class,
            HandleAppearance::class,
            \App\Http\Middleware\CorsMiddleware::class,
            HandleInertiaRequests::class,
            \App\Http\Middleware\ConfigureTenantDatabase::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (\Illuminate\Foundation\Configuration\Exceptions $exceptions) {
    })
    ->withCommands([
        \App\Console\Commands\TenantMigrateAllQueue::class,
    ])
    ->create();