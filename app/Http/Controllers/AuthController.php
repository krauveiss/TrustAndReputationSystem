<?php

namespace App\Http\Controllers;

use App\Exceptions\Admin\AttempToBanAdminException;
use App\Exceptions\Admin\BanUserIsAlreadyBannedException;
use App\Exceptions\Admin\NoTimeoutForUserException;
use App\Exceptions\Admin\UnbanUserIsNotBannedException;
use App\Http\Requests\Admin\BanRequest;
use App\Http\Requests\Admin\ChangeUserRoleRequest;
use App\Http\Requests\Admin\UnbanRequest;
use App\Http\Requests\Admin\UnTimeOutRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Reputation;
use App\Models\Role;
use App\Models\User;
use App\Services\LogService;
use App\Services\PenaltyService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{

    public function __construct(protected PenaltyService $penalty_service, protected LogService $log_service) {}
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

        try {
            $user = User::find($unbanRequest->user_id);
            $this->penalty_service->unban($user, $unbanRequest->user());
        } catch (UnbanUserIsNotBannedException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            report($e);
            return response()->json(['message' => 'Something went wrong...'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json(['message' => 'Now user is active'], Response::HTTP_OK);
    }

    public function ban(BanRequest $banRequest)
    {
        Gate::authorize('admin');

        try {
            $user = User::find($banRequest->user_id);
            $this->penalty_service->force_ban($user, $banRequest->user());
        } catch (BanUserIsAlreadyBannedException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (AttempToBanAdminException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            report($e);
            return response()->json(['message' => 'Something went wrong...'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json(['message' => 'Now user is banned'], Response::HTTP_OK);
    }

    public function untimeout(UnTimeOutRequest $unTimeOutRequest)
    {
        Gate::authorize('admin');
        try {
            $user = User::find($unTimeOutRequest->user_id);
            $this->penalty_service->untimeout($user, $unTimeOutRequest->user());
        } catch (NoTimeoutForUserException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            report($e);
            return response()->json(['message' => 'Something went wrong...'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json(['message' => 'Now user is active'], Response::HTTP_OK);
    }

    public function change_user_role(ChangeUserRoleRequest $changeUserRoleRequest)
    {
        Gate::authorize('admin');

        $user = User::find($changeUserRoleRequest->user_id);
        $role = $changeUserRoleRequest->role;

        DB::transaction(function () use ($user, $role) {
            $user->role_id = $role;
            $user->save();
        });
        $roleN = Role::find($role);
        $this->log_service->log($user, request()->user(), 'changed role', "new role: {$role}($roleN->name)");

        return response()->json(['text' => 'Success'], 200);
    }
}
