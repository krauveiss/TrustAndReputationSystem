<?php

namespace App\Services;

use App\Models\Report;
use App\Models\User;
use Exception;
use Illuminate\Support\Carbon;

class ReportService
{

    public function sendReport($user, $target_name, $reason)
    {
        try {
            if (!$user || $user->status == "timeout" || $user->status == "banned") {
                return [["text" => "Capabilities are limited."], 403];
            }

            $target = User::where('name', $target_name)->first();
            if (!$target) {
                return [["text" => "Wrong user"], 404];
            }
            if ($user->id == $target->id) {
                return [["text" => "Unable to submit a complaint against yourself"], 403];
            }


            $lastReport = Report::where('target_user_id', $target->id)->where('reporter_id', $user->id)->where('created_at', '>=', Carbon::now()->subHours(24))->exists();
            if ($lastReport) {
                return [["text" => 'This user has already been reported within the last 24 hours.'], 403];
            }

            $report = Report::create([
                'reporter_id' => $user->id,
                'target_user_id' => $target->id,
                'reason' => $reason
            ]);
            return [["text" => "The complaint has been registered", "report_id" => $report->id], 201];
        } catch (Exception) {
            return [["text" => "Something gone wrong"], 500];
        }
    }
    public function changeStatus($moderator, $report_id, $status)
    {
        $report = Report::find($report_id);

        $report->update([
            'status' => $status,
            'moderator_id' => $moderator->id
        ]);
        $report->save();
        return [["text" => "The complaint has been updated", "report_id" => $report->id], 200];
    }
}
