<?php

/*
| PHP 8.5 mendeprecate PDO::MYSQL_ATTR_SSL_CA (ganti ke Pdo\Mysql::ATTR_SSL_CA).
| Vendor Laravel 11 masih pakai konstanta lama meskipun config/database.php
| lokal sudah pakai yang baru. Suppress hanya di 8.5+ agar production aman.
*/
if (PHP_VERSION_ID >= 80500) {
    set_error_handler(function ($severity, $message, $file, $line) {
        if (str_contains($message, 'MYSQL_ATTR_SSL_CA')) {
            return true;
        }

        return false;
    }, E_DEPRECATED);
}

use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
        $middleware->web(append: [
            SecurityHeaders::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'webhook/fonnte/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
