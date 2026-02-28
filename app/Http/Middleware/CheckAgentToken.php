<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Asset;

class CheckAgentToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Agent-Token');

        if (!$token) {
            return response()->json(['message' => 'Missing Agent Token'], 401);
        }

        $asset = Asset::where('api_token', $token)->first();

        if (!$asset) {
            return response()->json(['message' => 'Invalid Agent Token'], 401);
        }

        // Authenticate the request so $request->user() returns the Asset model
        $request->setUserResolver(function () use ($asset) {
            return $asset;
        });

        // Also merge for backwards compatibility if needed
        $request->merge(['agent' => $asset]);

        return $next($request);
    }
}
