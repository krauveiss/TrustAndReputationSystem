<?php

namespace App\Services;

use App\Exceptions\Report\ReportFloodException;
use App\Exceptions\Report\ReportNotFoundException;
use App\Exceptions\Report\SelfReportException;
use App\Exceptions\Report\WrongUserException;
use App\Models\Report;
use App\Models\User;
use Exception;
use Illuminate\Support\Carbon;

class ReportService
{

    public function sendReport($user, $target_name, $reason): void
    {

        $target = User::where('name', $target_name)->first();
        if (!$target) {
            throw new WrongUserException();
        }
        if ($user->id == $target->id) {
            throw new SelfReportException();
        }
        if ($target->role->name == 'admin') {
            throw new WrongUserException();
        }


        $lastReport = Report::where('target_user_id', $target->id)->where('reporter_id', $user->id)->where('created_at', '>=', Carbon::now()->subHours(24))->exists();
        if ($lastReport) {
            throw new ReportFloodException();
        }

        Report::create([
            'reporter_id' => $user->id,
            'target_user_id' => $target->id,
            'reason' => $reason
        ]);
    }
    public function changeStatus($moderator, $report_id, $status): void
    {
        $report = Report::find($report_id);
        if (!$report) {
            throw new ReportNotFoundException();
        }
        $report->update([
            'status' => $status,
            'moderator_id' => $moderator->id
        ]);
        $report->save();
    }
}
