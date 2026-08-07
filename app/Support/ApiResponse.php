<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public const ENCODING_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION;

    /**
     * @param  array<string, mixed>  $data
     */
    public static function success(string $message = 'Success', array $data = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status, [], static::ENCODING_OPTIONS);
    }

    /**
     * @param  array<string, mixed>  $errors
     */
    public static function error(string $message = 'Something went wrong', int $status = 400, array $errors = []): JsonResponse
    {
        $body = [
            'success' => false,
            'message' => $message,
            'data' => null,
        ];

        if ($errors !== []) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $status, [], static::ENCODING_OPTIONS);
    }

    public static function validation(array $errors, string $message = 'The given data was invalid.'): JsonResponse
    {
        return static::error($message, 422, $errors);
    }

    public static function notFound(string $message = 'Resource not found.'): JsonResponse
    {
        return static::error($message, 404);
    }

    public static function unauthorized(string $message = 'Unauthenticated.'): JsonResponse
    {
        return static::error($message, 401);
    }
}
