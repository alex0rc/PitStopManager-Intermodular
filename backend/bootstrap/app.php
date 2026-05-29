<?php

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
        $middleware->trustProxies(at: '*');
        $middleware->statefulApi();
        $middleware->alias([
            'role'   => \App\Http\Middleware\RoleMiddleware::class,
            'admin'  => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
        ]);
        $middleware->validateCsrfTokens(except: ['api/*', 'webhooks/*']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            $status = $e->getStatusCode();
            $message = $e->getMessage() ?: match ($status) {
                403 => 'No tienes permiso para esta acción.',
                404 => 'Recurso no encontrado.',
                405 => 'Método no permitido.',
                429 => 'Demasiadas solicitudes.',
                default => 'Error en la solicitud.',
            };

            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => $message], $status);
            }

            return null;
        });
    })->create();
