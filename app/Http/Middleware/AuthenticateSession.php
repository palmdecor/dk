<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthenticateSession
{
    public function handle($request, Closure $next)
    {
        if ($request->user()) {
            Auth::shouldUse('web');
        }

        return $next($request);
    }
}
