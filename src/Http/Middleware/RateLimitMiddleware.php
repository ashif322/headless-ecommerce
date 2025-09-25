<?php

namespace Webkul\GraphQLAPI\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Webkul\GraphQLAPI\Validators\CustomException;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('api')->user();

        $key = $user ? "graphql|{$user->id}" : "graphql|{$request->ip()}";

        if (RateLimiter::tooManyAttempts($key, 60)) {
            throw new CustomException(trans('bagisto_graphql::app.rate-limit.too-many-attempts'));
        }

        RateLimiter::hit($key);

        return $next($request);
    }
}
