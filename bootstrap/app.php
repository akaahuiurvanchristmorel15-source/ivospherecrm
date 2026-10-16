<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnforceTenantAccess;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckRole::class,
            'permission' => CheckPermission::class,
        ]);

        // Espaces abonnés : accès limité aux modules du plan et à un abonnement actif.
        $middleware->appendToGroup('web', EnforceTenantAccess::class);

        // Retours et notifications du prestataire de paiement (état toujours revérifié par son API).
        $middleware->preventRequestForgery(except: [
            'offres/paiement/notification',
            'offres/paiement/*/retour',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (PostTooLargeException $exception, Request $request) {
            $message = 'Les fichiers envoyés sont trop volumineux pour le serveur. Réduisez la taille ou le nombre de photos, puis réessayez.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 413);
            }

            return back()->with('error', $message);
        });
    })->create();
