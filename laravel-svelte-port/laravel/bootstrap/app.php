<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        apiPrefix: 'api/v1',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        
        // Register middleware aliases for use in routes
        $middleware->alias([
            'validate.bot.access' => \App\Http\Middleware\ValidateBotAccess::class,
            'platform.app.auth' => \App\Http\Middleware\PlatformAppAuthentication::class,
            'validate.platform.permissible' => \App\Http\Middleware\ValidatePlatformPermissible::class,
            'account.admin' => \App\Http\Middleware\EnsureAccountAdmin::class,
            'permission' => \App\Http\Middleware\CheckCustomRolePermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
