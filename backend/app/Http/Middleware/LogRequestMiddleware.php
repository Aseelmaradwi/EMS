<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequestMiddleware
{
    /**
     * @var array<int, string>
     */
    private array $sensitiveFields = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'token',
        'refresh_token',
        'access_token',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);

        $response = $next($request);

        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        $payload = $request->except($this->sensitiveFields);
        $user = $request->user('api');

        Log::info('EMS API request processed', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'user_id' => $user?->id,
            'payload' => $payload,
            'status' => $response->getStatusCode(),
            'duration_ms' => $durationMs,
        ]);

        return $response;
    }
}
