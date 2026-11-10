<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectUsersTo('/admin/dashboard');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            // Si la sesión ya está autenticada, no tiene sentido mostrar la
            // pantalla de "página expirada": se envía al panel.
            if (Auth::check()) {
                return redirect()->route('admin.dashboard');
            }

            $message = 'La página expiró por inactividad. Vuelve a ingresar.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $message], 419);
            }

            return redirect()->route('login')->withErrors(['email' => $message]);
        });
    })->create();
