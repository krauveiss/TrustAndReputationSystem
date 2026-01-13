<?php

namespace App\Http\Middleware;

use App\Models\Penalty;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTimeout
{

    public function handle(Request $request, Closure $next): Response
    {
        $user = request()->user();
        if ($user->status == 'timeout') {
            $penalty = Penalty::where('user_id', $user->id)->where('type', 'temporary_block')->latest()->first();
            if (!$penalty) {
                $user->status == 'active';
                $user->save();
                return $next($request);
            } else {
                return response(['text' => "Your account has been suspended for violating the rules.", 'unban_date' => $penalty->expires_at], 403);
            }
        }
        return $next($request);
    }
}
