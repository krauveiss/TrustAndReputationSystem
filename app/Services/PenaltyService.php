<?php

namespace App\Services;

use App\Exceptions\Admin\AttempToBanAdminException;
use App\Exceptions\Admin\BanUserIsAlreadyBannedException;
use App\Exceptions\Admin\NoTimeoutForUserException;
use App\Exceptions\Admin\UnbanUserIsNotBannedException;
use App\Models\Penalty;
use Exception;
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
        if ($user->role_id == '3') {
            return [["text" => 'Security error'], 403];
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

    public function unban($user, $admin): void
    {

        if ($user->status != 'banned') {
            throw new UnbanUserIsNotBannedException();
        }

        DB::transaction(function () use ($user, $admin) {
            $user->status = 'active';
            $user->save();
        });
        Penalty::create([
            'user_id' => $user->id,
            'type' => 'unban',
            'initiator' => $admin->id
        ]);
    }

    public function force_ban($user, $admin): void
    {
        if ($user->status == 'banned') {
            throw new BanUserIsAlreadyBannedException();
        }
        if ($user->role_id == '3') {
            throw new AttempToBanAdminException();
        }

        DB::transaction(function () use ($user, $admin) {
            $user->status = 'banned';
            $user->save();
        });
        Penalty::create([
            'user_id' => $user->id,
            'type' => 'permanent_block',
            'initiator' => $admin->id
        ]);
    }

    public function untimeout($user, $admin): void
    {

        if ($user->status != 'timeout') {
            throw new NoTimeoutForUserException();
        }
        $penalty = Penalty::where('user_id', $user->id)->where('type', 'temporary_block')->latest()->first();
        if (!$penalty) {
            throw new NoTimeoutForUserException();
        }

        $penalty->expires_at = Carbon::now();
        $penalty->save();
        DB::transaction(function () use ($user, $admin) {
            $user->status = 'active';
            $user->save();

            Penalty::create([
                'user_id' => $user->id,
                'type' => 'untimeout',
                'initiator' => $admin->id
            ]);
        });
    }
}
