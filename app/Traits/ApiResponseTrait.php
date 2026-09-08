<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponseTrait
{
    /**
     * Return a standardized success JSON response.
     *
     * @param  array<string, string>  $headers
     */
    public function successResponse(
        mixed $data = null,
        string $message = 'Operation successful',
        int $code = Response::HTTP_OK,
        array $headers = []
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
        ], $code, $headers);
    }

    /**
     * Return a standardized error JSON response.
     *
     * @param  array<string, string>  $headers
     */
    public function errorResponse(
        string $message = 'An error occurred',
        int $code = Response::HTTP_BAD_REQUEST,
        mixed $errors = null,
        array $headers = []
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $code, $headers);
    }
}
