<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',   // token-auth mobile API (Sanctum)
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\Localization::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/moyasar',
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'is_internal' => \App\Http\Middleware\IsInternal::class,
            'api.internal' => \App\Http\Middleware\EnsureApiInternal::class,
            'is_manager' => \App\Http\Middleware\IsManager::class,
            'can_manage_projects' => \App\Http\Middleware\CanManageProjects::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API requests always get JSON (never an HTML error page or login redirect).
        $exceptions->shouldRenderJsonWhen(
            fn (\Illuminate\Http\Request $request, \Throwable $e) => $request->is('api/*') || $request->expectsJson()
        );

        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $e, \Illuminate\Http\Request $request): \Symfony\Component\HttpFoundation\Response {
            // Never swap an API response for an HTML error view.
            if ($request->is('api/*') || $request->expectsJson()) {
                return $response;
            }

            if (! app()->environment(['local', 'testing'])) {
                $status = $response->getStatusCode();

                if (in_array($status, [403, 404, 500, 503], true) && view()->exists("errors.{$status}")) {
                    return response()->view("errors.{$status}", [], $status);
                }
            }

            return $response;
        });
    })->create();
