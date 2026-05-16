<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Return a successful API response envelope.
     */
    public static function success(
        string $message,
        array $data = [],
        array $meta = [],
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
        ], $status);
    }

    /**
     * Return a failed API response envelope.
     */
    public static function error(
        string $message,
        array $errors = [],
        string $code = 'ERROR',
        int $status = 400
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'code' => $code,
        ], $status);
    }

    private function __construct()
    {
    }
}
