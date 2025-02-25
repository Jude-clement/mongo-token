<?php

namespace App\Http\Controllers;

use App\Models\UserModel;
use App\Models\PersonalAccessToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // Create a new user
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        // Check if user already exists
        $existingUser = UserModel::where('email', $validatedData['email'])->first();
        if ($existingUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'User with this email already exists'
            ], 422);
        }

        $user = UserModel::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    // Login function
    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required',
        ]);

        $user = UserModel::where('name', $request->name)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        // Create token with 1 minute expiration
        $plainTextToken = Str::random(40);
        $token = hash('sha256', $plainTextToken);
        
        $personalToken = PersonalAccessToken::create([
            'tokenable_type' => get_class($user),
            'tokenable_id' => (string) $user->_id,
            'name' => 'auth_token',
            'token' => $token,
            'abilities' => ['*'],
            'expires_at' => now()->addMinute()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User logged in',
            'token' => $personalToken->_id . '|' . $plainTextToken
        ]);
    }

    // Get all users (protected route)
    public function getAllUsers(Request $request)
    {
        $users = UserModel::all();
        
        return response()->json([
            'status' => 'success',
            'users' => $users
        ]);
    }

    // Logout function
    public function logout(Request $request)
    {
        // Extract token from authorization header
        $authHeader = $request->header('Authorization');
        if (Str::startsWith($authHeader, 'Bearer ')) {
            $token = Str::substr($authHeader, 7);
            
            // Find and delete token
            list($id, $token) = explode('|', $token, 2);
            
            PersonalAccessToken::where('_id', $id)->delete();
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Token deleted successfully'
        ]);
    }
}