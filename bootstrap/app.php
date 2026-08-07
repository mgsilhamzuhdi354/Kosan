<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_FOR
            | Request::HEADER_X_FORWARDED_HOST
            | Request::HEADER_X_FORWARDED_PORT
            | Request::HEADER_X_FORWARDED_PROTO);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            $messages = [
                403 => 'Akses tidak diizinkan.',
                419 => 'Sesi berakhir. Silakan coba lagi.',
                422 => 'Aksi tidak dapat diproses.',
            ];

            $status = $exception->getStatusCode();

            if (! array_key_exists($status, $messages)) {
                return null;
            }

            $message = $exception->getMessage() ?: $messages[$status];
            $target = $request->headers->get('referer')
                ? redirect()->back()
                : redirect()->route($request->user() ? 'dashboard' : 'home');

            return $target->with('error', $message);
        });
    })->create();
