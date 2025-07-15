<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateInternalToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $internalAccessToken = config('services.mais_entregas.internal_access_token');

        if (is_null($request->header('X-Mais-Entregas-Token')) || $request->header('X-Mais-Entregas-Token') !== $internalAccessToken)
            return response()->json(['message' => 'Access denied.'], Response::HTTP_UNAUTHORIZED);

        return $next($request);
    }
}
