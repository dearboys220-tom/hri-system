<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ])->alias([
            'company.verified' => \App\Http\Middleware\EnsureCompanyVerified::class,
            'role' => \App\Http\Middleware\EnsureUserHasCorrectRole::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {

            if (
                $request->is('admin/*') ||
                $request->is('investigator/*') ||
                $request->is('reviewer/*')
            ) {
                return route('login-staff');
            }

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
