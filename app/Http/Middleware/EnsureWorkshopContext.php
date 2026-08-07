<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkshopContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->workshop_id === null) {
            return response()->json([
                'success' => false,
                'message' => 'Workshop setup required.',
                'data' => null,
            ], 428);
        }

        return $next($request);
    }
}
