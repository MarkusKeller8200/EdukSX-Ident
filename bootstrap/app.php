<?php

use App\Http\Middleware\VerifyZitadelSignature;
use EduKSX\Core\Http\Problem;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Hinter Traefik: HTTPS und Client-IP aus den Proxy-Headern
        $middleware->trustProxies(at: '*');
        $middleware->alias(['zitadel.signature' => VerifyZitadelSignature::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Fehler der API als application/problem+json aus eduksx/core (API-Standard A6)
        Problem::register($exceptions);
    })->create();
