<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class OptionalAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if ($token) {

            $accessToken = PersonalAccessToken::findToken($token);

            if ($accessToken) {

                auth()->setUser($accessToken->tokenable);

            }

        }

        return $next($request);
    }
}
