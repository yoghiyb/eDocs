<?php

namespace App\Http\Middleware;

use Closure;

class AdminOrManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->user() && $request->user()->role == 3) {
            return response()->json(['error' => 'Anda tidak memiliki hak akses'], 503);
        }
        return $next($request);
    }
}
