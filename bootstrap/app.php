<?php

use App\Http\Middleware\SanitizeInput;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            SanitizeInput::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'status' => 422,
                    'error' => [
                        'message' => $e->validator->errors()->first() ?: 'The given data was invalid.',
                        'details' => $e->validator->errors()->toArray(),
                    ],
                ], 422, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'status' => 404,
                    'error' => [
                        'message' => $e->getMessage() ?: 'Endpoint or resource not found.',
                    ],
                ], 404, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'status' => 401,
                    'error' => [
                        'message' => 'Unauthenticated.',
                    ],
                ], 401, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'status' => 405,
                    'error' => [
                        'message' => 'Method Not Allowed.',
                    ],
                ], 405, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        });
    })->create();
