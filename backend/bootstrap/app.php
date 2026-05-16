<?php

use App\Http\Middleware\LogRequestMiddleware;
use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(static fn () => null);
        $middleware->appendToGroup('api', LogRequestMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $exception, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
            }

            return null;
        });

        $exceptions->render(function (ValidationException $exception, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('Validation failed.', $exception->errors(), 'VALIDATION_ERROR', 422);
            }

            return null;
        });

        $exceptions->render(function (UnauthorizedHttpException $exception, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
            }

            return null;
        });

        $exceptions->render(function (NotFoundHttpException $exception, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('Not Found.', [], 'ROUTE_NOT_FOUND', 404);
            }

            return null;
        });

        $exceptions->render(function (MethodNotAllowedHttpException $exception, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('Method Not Allowed.', [], 'METHOD_NOT_ALLOWED', 405);
            }

            return null;
        });

        $exceptions->render(function (HttpExceptionInterface $exception, $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $statusCode = $exception->getStatusCode();

            if ($statusCode === 401) {
                return ApiResponse::error('Unauthenticated.', [], 'AUTH_UNAUTHORIZED', 401);
            }

            if ($statusCode === 403) {
                return ApiResponse::error('Forbidden.', [], 'AUTH_FORBIDDEN', 403);
            }

            if ($statusCode === 404) {
                return ApiResponse::error('Not Found.', [], 'NOT_FOUND', 404);
            }

            return ApiResponse::error(
                $exception->getMessage() !== '' ? $exception->getMessage() : 'Request failed.',
                [],
                'HTTP_ERROR',
                $statusCode
            );
        });

        $exceptions->render(function (Throwable $exception, $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            Log::error('Unhandled API exception', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return ApiResponse::error('Server error.', [], 'INTERNAL_SERVER_ERROR', 500);
        });
    })->create();
