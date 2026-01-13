<?php

namespace App\Services;

use App\Models\Reputation;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Support\Facades\DB;

class ViolationService
{
    protected ReputationService $reputation_service;

    public function __construct(ReputationService $reputation_service)
    {
        $this->reputation_service = $reputation_service;
    }
    public function addViolation($moderator, $user_id, $type, $severity)
    {
        $user = User::find($user_id);
        if (!$user) {
            return [['text' => 'Not found user'], 404];
        }
        if ($user->role->name != 'user') {
            $moderator->role_id = 1;
            $moderator->save();
            return [['text' => 'Suspicious activity, rights revoked'], 403];
        }
        $score_fine = 0;
        switch ($severity) {
            case "minor":
                $score_fine = 5;
                break;
            case "major":
                $score_fine = 20;
                break;
            case "critical":
                $score_fine = 50;
                break;
        }

        Violation::create([
            'user_id' => $user->id,
            'type' => $type,
            'severity' => $severity,
            'moderator_id' => $moderator->id
        ]);

        $this->reputation_service->changeScore($user, $score_fine);
        return [['text' => 'Violation added, reputation changed'], 200];
    }
}
