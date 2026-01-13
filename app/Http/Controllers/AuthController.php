<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $registerRequest)
    {
        $user = User::create([
            'name' => $registerRequest->name,
            'email' => $registerRequest->email,
            'password' => Hash::make($registerRequest->password),
            'role_id' => Role::where('name', 'user')->value('id')
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'role' => $user->role
        ]);
    }

    public function login(LoginRequest $loginRequest)
    {
        $user = User::where('email', $loginRequest->email)->first();

        if (!$user || !Hash::check($loginRequest->password, $user->password)) {
            return response()->json([
                'message' => 'invalid credentials'
            ], 401);
        }

        return response()->json([
            'token' => $user->createToken('api')->plainTextToken
        ]);
    }


    public function me()
    {
        return response()->json(['role' => request()->user()->role['name']]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([], 200);
    }
}
