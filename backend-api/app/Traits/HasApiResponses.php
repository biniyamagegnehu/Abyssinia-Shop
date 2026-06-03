<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait HasApiResponses
{
    /**
     * Return a success JSON response.
     */
    protected function successResponse(mixed $data = [], string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return an error JSON response.
     */
    protected function errorResponse(string $message = 'Error occurred', int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }
}
