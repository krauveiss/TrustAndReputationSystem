<?php

namespace App\Services;

use App\Exceptions\Violations\SuspiciousActivityException;
use App\Exceptions\Violations\UserNotFoundException;
use App\Exceptions\Violations\ViolationNotFoundException;
use App\Models\Reputation;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Array_;

class ViolationService
{
    protected ReputationService $reputation_service;
    protected LogService $log_service;

    public function __construct(ReputationService $reputation_service, LogService $log_service)
    {
        $this->reputation_service = $reputation_service;
        $this->log_service = $log_service;
    }
    public function addViolation($moderator, $user_id, $type, $severity, $comment = ''): void
    {
        $user = User::find($user_id);
        if (!$user) {
            throw new UserNotFoundException();
        }
        if ($user->role->name != 'user') {
            $moderator->role_id = 1;
            $moderator->save();
            $this->log_service->log($user_id, $moderator, 'prohibited action(add violation)', "params: {$type}({$severity})");
            throw new SuspiciousActivityException();
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
            'moderator_id' => $moderator->id,
            'comment' => $comment
        ]);

        $this->reputation_service->changeScore($user, $score_fine);
    }

    public function changeViolationStatus($violation_id, $status, $comment = ""): void
    {
        $violation = Violation::find($violation_id);
        if (!$violation) {
            throw new ViolationNotFoundException();
        }
        $violation->status = $status;
        $violation->comment = $comment;
        $violation->save();
    }
}
