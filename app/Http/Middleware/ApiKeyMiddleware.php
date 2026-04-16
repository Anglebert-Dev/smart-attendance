<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $incoming = $request->header('X-API-Key') ?? $request->bearerToken();

        if (!$incoming) {
            return response()->json(['error' => 'API key required'], 401);
        }

        $apiKey = ApiKey::verify($incoming);

        if (!$apiKey) {
            return response()->json(['error' => 'Invalid or revoked API key'], 401);
        }

        $request->attributes->set('api_key', $apiKey);

        return $next($request);
    }
}
