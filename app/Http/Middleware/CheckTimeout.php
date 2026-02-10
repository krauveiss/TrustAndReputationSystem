<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use App\Models\Penalty;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTimeout
{

    public function handle(Request $request, Closure $next): Response
    {
        $user = request()->user();
        if ($user->status == UserStatus::TIMEOUT) {
            $penalty = Penalty::where('user_id', $user->id)->where('type', 'temporary_block')->latest()->first();
            if (!$penalty) {
                $user->status = UserStatus::ACTIVE;
                $user->save();
                return $next($request);
            } else {
                if ($penalty->expires_at <= Carbon::now()) {
                    $user->status = UserStatus::ACTIVE;
                    $user->save();
                    return $next($request);
                }
                return response(['text' => "Your account has been suspended for violating the rules.", 'unban_date' => $penalty->expires_at], 403);
            }
        }
        return $next($request);
    }
}
