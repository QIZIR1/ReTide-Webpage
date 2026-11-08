<?php

/*
|--------------------------------------------------------------------------
| THIS IS THE Bootstrap/App.php file
|--------------------------------------------------------------------------
*/

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\RedirectIfAdmin;
use App\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeadersMiddleware::class);

        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'not_admin' => RedirectIfAdmin::class,
        ]);
//penambahan append
        $middleware->append(\App\Http\Middleware\AssignRequestId::class);

        $middleware->validateCsrfTokens(except: [
            '/payment/notification',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

// Tell Laravel to use the Vercel /tmp storage path defined in index.php
if (isset($_SERVER['LARAVEL_STORAGE_PATH'])) {
    $app->useStoragePath($_SERVER['LARAVEL_STORAGE_PATH']);
}

return $app;
