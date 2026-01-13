<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reports\ChangeReportStatusRequest;
use App\Http\Requests\Reports\ReportRequest;
use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function __construct(private ReportService $report_service) {}

    public function store(ReportRequest $reportRequest)
    {
        $result = $this->report_service->sendReport(request()->user(), $reportRequest->target_name, $reportRequest->reason);
        return response()->json($result[0], $result[1]);
    }

    public function update(ChangeReportStatusRequest $changeReportStatusRequest)
    {
        if (Gate::denies("moderator")) {
            abort(404);
        }

        $result = $this->report_service->changeStatus(request()->user(), $changeReportStatusRequest->report_id, $changeReportStatusRequest->status);
        return response()->json($result[0], $result[1]);
    }
}
