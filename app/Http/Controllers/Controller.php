<?php

namespace App\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class Controller
{
    /**
     * Log an exception with full context and return a consistent error response.
     *
     * The real exception detail is only written to the log file; the client
     * always receives a generic, user-friendly message.
     */
    protected function apiError(Throwable $e, string $context, int $status = 500): JsonResponse
    {
        Log::error("[{$context}] ".$e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'user_id' => auth()->id(),
        ]);

        return ApiResponse::error('Something went wrong. Please try again.', $status);
    }
}
