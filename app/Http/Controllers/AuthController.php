<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\BanRequest;
use App\Http\Requests\Admin\UnbanRequest;
use App\Http\Requests\Admin\UnTimeOutRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Reputation;
use App\Models\Role;
use App\Models\User;
use App\Services\PenaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function __construct(protected PenaltyService $penalty_service) {}
    public function register(RegisterRequest $registerRequest)
    {
        $user = User::create([
            'name' => $registerRequest->name,
            'email' => $registerRequest->email,
            'password' => Hash::make($registerRequest->password),
            'role_id' => Role::where('name', 'user')->value('id')
        ]);

        Reputation::create([
            'user_id' => $user->id,
            'score' => 50,
            'level' => 'medium'
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

    public function unban(UnbanRequest $unbanRequest)
    {
        Gate::authorize('admin');

        $user = User::find($unbanRequest->user_id);
        $result = $this->penalty_service->unban($user, $unbanRequest->user());

        return response()->json($result[0], $result[1]);
    }

    public function ban(BanRequest $banRequest)
    {
        Gate::authorize('admin');

        $user = User::find($banRequest->user_id);
        $result = $this->penalty_service->force_ban($user, $banRequest->user());

        return response()->json($result[0], $result[1]);
    }



    public function untimeout(UnTimeOutRequest $unTimeOutRequest)
    {
        Gate::authorize('admin');
        $user = User::find($unTimeOutRequest->user_id);
        $result = $this->penalty_service->untimeout($user, $unTimeOutRequest->user());

        return response()->json($result[0], $result[1]);
    }
}
