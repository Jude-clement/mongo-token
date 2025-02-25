<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;

class HandleSanctumUnauthorized
{
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (AuthenticationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid action'
            ], 401);
        }
    }
}
