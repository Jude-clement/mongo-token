<?php

namespace App\Http\Middleware;

use App\Models\PersonalAccessToken;
use App\Models\UserModel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MongoDBAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !Str::startsWith($authHeader, 'Bearer ')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid action'
            ], 401);
        }
        
        $token = Str::substr($authHeader, 7);
        
        if (Str::contains($token, '|')) {
            list($id, $plainTextToken) = explode('|', $token, 2);
            
            $accessToken = PersonalAccessToken::where('_id', $id)
                ->where('token', hash('sha256', $plainTextToken))
                ->first();
                
            if (
                $accessToken && 
                (!$accessToken->expires_at || $accessToken->expires_at->isFuture())
            ) {
                // Token is valid, find the user
                $user = UserModel::where('_id', $accessToken->tokenable_id)->first();
                
                if ($user) {
                    // Set user on request
                    $request->setUserResolver(function () use ($user, $accessToken) {
                        return $user->withAccessToken($accessToken);
                    });
                    
                    return $next($request);
                }
            }
        }
        
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid action'
        ], 401);
    }
}