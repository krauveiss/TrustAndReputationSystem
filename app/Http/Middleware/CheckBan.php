<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBan
{

    public function handle(Request $request, Closure $next): Response
    {
        if (request()->user()->status == UserStatus::BANNED) {
            return response()->json("Your account is banned", 403);
        }
        return $next($request);
    }
}
