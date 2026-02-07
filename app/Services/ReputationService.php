<?php

namespace App\Services;

use App\Exceptions\Violations\UserNotFoundException;
use App\Models\Reputation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReputationService
{
    protected PenaltyService $penalty_service;
    public function __construct(PenaltyService $penalty_service)
    {
        $this->penalty_service =  $penalty_service;
    }

    public function changeScore($user, $score_fine)
    {
        DB::transaction(function () use ($user, $score_fine) {
            $repObj = Reputation::where('user_id', $user->id)->first();
            $repObj->score = $repObj->score - $score_fine;
            $repObj->save();
        });
        $this->recalculateLevel($user);
    }


    public function setScore($user_id, $score): void
    {
        $user = User::find($user_id);
        if (!$user) {
            throw new UserNotFoundException();
        }
        DB::transaction(function () use ($user, $score) {
            $repObj = Reputation::where('user_id', $user->id)->first();
            $repObj->score = $score;
            $repObj->save();
        });
        $this->recalculateLevel($user);
    }


    protected function recalculateLevel($user)
    {
        DB::transaction(function () use ($user) {
            $repObj = Reputation::where('user_id', $user->id)->first();
            $score = $repObj->score;
            if ($score >= 80) {
                $repObj->level = 'high';
            } else if ($score >= 30 && $score <= 79) {
                $repObj->level = 'medium';
            } else {
                $repObj->level = 'low';
            }
            $repObj->save();
        });

        $now_score = Reputation::where('user_id', $user->id)->first()->score;
        if ($now_score <= -50) {
            $this->penalty_service->applyBan($user);
        } else if ($now_score <= 0 && $now_score > -50) {
            $this->penalty_service->applyTimeout($user);
        }
    }
}
