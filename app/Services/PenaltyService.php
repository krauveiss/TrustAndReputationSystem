<?php

namespace App\Services;

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

    public function unban($user, $admin)
    {
        try {
            if ($user->status != 'banned') {
                return [["text" => 'This user is not banned'], 400];
            }

            DB::transaction(function () use ($user) {
                $user->status = 'active';
                $user->save();
            });

            Penalty::create([
                'user_id' => $user->id,
                'type' => 'unban',
                'initiator' => $admin->id
            ]);

            return [["text" => 'Now user is active'], 200];
        } catch (Exception $ex) {
            return [["text" => 'Error'], 500];
        }
    }

    public function force_ban($user, $admin)
    {

        try {
            if ($user->status == 'banned') {
                return [["text" => 'This user is already banned'], 400];
            }

            DB::transaction(function () use ($user) {
                $user->status = 'banned';
                $user->save();
            });

            Penalty::create([
                'user_id' => $user->id,
                'type' => 'permanent_block',
                'initiator' => $admin->id
            ]);

            return [["text" => 'Now user is banned'], 200];
        } catch (Exception) {
            return [["text" => 'Error'], 500];
        }
    }

    public function untimeout($user, $admin)
    {
        try {
            if ($user->status != 'timeout') {
                return [['text' => 'No timeout for this user'], 400];
            }
            $penalty = Penalty::where('user_id', $user->id)->where('type', 'temporary_block')->latest()->first();
            if (!$penalty) {
                return [['text' => 'No timeout for this user'], 400];
            } else {
                $penalty->expires_at = Carbon::now();
                $penalty->save();
                DB::transaction(function () use ($user) {
                    $user->status = 'active';
                    $user->save();
                });
                Penalty::create([
                    'user_id' => $user->id,
                    'type' => 'untimeout',
                    'initiator' => $admin->id
                ]);
                return [['text' => 'User is active now'], 200];
            }
        } catch (Exception) {
            return [["text" => 'Error'], 500];
        }
    }
}
