<?php

namespace App\Http\Controllers;

use App\Models\UserModel;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Invalid action'], 403);
        }

        $users = UserModel::all(); // MongoDB does not use SQL-style queries

        return response()->json($users);
    }
}
