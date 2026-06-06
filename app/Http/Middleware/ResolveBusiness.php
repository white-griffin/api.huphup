<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveBusiness
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $provider = auth('provider')->user();

        $businessId = $request->header('X-Business-Id');

        if (!$businessId) {
            abort(400, 'Business header missing');
        }

        $business = $provider->businesses()
            ->whereKey($businessId)
            ->first();

        if (!$business) {
            abort(403, 'Business not owned by provider');
        }

        app()->instance('business', $business);

        return $next($request);
    }
}
