<?php

namespace App\Services;

use App\Enums\PenaltyType;
use App\Enums\UserStatus;
use App\Exceptions\Admin\AttempToBanAdminException;
use App\Exceptions\Admin\BanUserIsAlreadyBannedException;
use App\Exceptions\Admin\NoTimeoutForUserException;
use App\Exceptions\Admin\UnbanUserIsNotBannedException;
use App\Exceptions\Penalty\UserIsAlreadyBannedException;
use App\Models\Penalty;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PenaltyService
{

    public function applyBan($user)
    {
        DB::transaction(function () use ($user) {
            $user->status = UserStatus::BANNED;
            $user->save();

            Penalty::create([
                'user_id' => $user->id,
                'type' =>  PenaltyType::PERM_BLOCK
            ]);
        });
    }

    public function applyTimeout($user)
    {
        if ($user->status == UserStatus::BANNED) {
            throw new UserIsAlreadyBannedException();
        }
        DB::transaction(function () use ($user) {
            $user->status = UserStatus::TIMEOUT;
            $user->save();

            Penalty::create([
                'user_id' => $user->id,
                'type' => PenaltyType::TEMP_BLOCK,
                'expires_at' => Carbon::now()->addDays(1)
            ]);
        });
    }

    public function unban($user, $admin): void
    {

        if ($user->status != UserStatus::BANNED) {
            throw new UnbanUserIsNotBannedException();
        }

        DB::transaction(function () use ($user, $admin) {
            $user->status = UserStatus::ACTIVE;
            $user->save();

            Penalty::create([
                'user_id' => $user->id,
                'type' => PenaltyType::UNBAN,
                'initiator' => $admin->id
            ]);
        });
    }

    public function force_ban($user, $admin): void
    {
        if ($user->status == UserStatus::BANNED) {
            throw new BanUserIsAlreadyBannedException();
        }
        if ($user->role_id == '3') {
            throw new AttempToBanAdminException();
        }

        DB::transaction(function () use ($user, $admin) {
            $user->status = UserStatus::BANNED;
            $user->save();

            Penalty::create([
                'user_id' => $user->id,
                'type' => PenaltyType::PERM_BLOCK,
                'initiator' => $admin->id
            ]);
        });
    }

    public function untimeout($user, $admin): void
    {

        if ($user->status != UserStatus::TIMEOUT) {
            throw new NoTimeoutForUserException();
        }
        $penalty = Penalty::where('user_id', $user->id)->where('type', 'temporary_block')->latest()->first();
        if (!$penalty) {
            throw new NoTimeoutForUserException();
        }

        $penalty->expires_at = Carbon::now();
        $penalty->save();
        DB::transaction(function () use ($user, $admin) {
            $user->status = UserStatus::ACTIVE;
            $user->save();

            Penalty::create([
                'user_id' => $user->id,
                'type' => PenaltyType::UNTIMEOUT,
                'initiator' => $admin->id
            ]);
        });
    }
}
