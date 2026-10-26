<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [\App\Http\Middleware\EnsureUserIsActive::class]);
        // Check revoked sessions before auth redirects or CSRF rejects their next request.
        $middleware->appendToPriorityList(
            \Illuminate\Session\Middleware\StartSession::class,
            \App\Http\Middleware\EnsureUserIsActive::class
        );

        $middleware->alias([
        'admin' => \App\Http\Middleware\AdminMiddleware::class,]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
