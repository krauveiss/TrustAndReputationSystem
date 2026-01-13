<?php

namespace App\Services;

use App\Models\Penalty;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PenaltyService
{

    public function applyBan($user)
    {
        DB::transaction(function () use ($user) {
            $user->status = 'banned';
            $user->save();
        });

        Penalty::create([
            'user_id' => $user->id,
            'type' => 'permanent_block'
        ]);
    }

    public function applyTimeout($user)
    {
        if ($user->status == 'banned') {
            return;
        }
        DB::transaction(function () use ($user) {
            $user->status = 'timeout';
            $user->save();
        });

        Penalty::create([
            'user_id' => $user->id,
            'type' => 'temporary_block',
            'expires_at' => Carbon::now()->addDays(1)
        ]);
    }
}
