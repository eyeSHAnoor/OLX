<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {

            if (auth()->user()->hasRole('super_admin')) {
                return redirect('/dashboard');
            }

            return redirect('/');
        }

        return $next($request);
    }
}

