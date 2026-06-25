<?php

namespace App\Traits;
use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    public function successResponse(mixed $data = null, string $message = 'Suceess', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public function errorResponse(mixed $data = null, string $message = 'Something went wrong', int $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}