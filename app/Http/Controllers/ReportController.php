<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetReportsRequest;
use App\Http\Requests\Reports\ChangeReportStatusRequest;
use App\Http\Requests\Reports\ReportRequest;
use App\Http\Resources\ReportResource;
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

    public function index(GetReportsRequest $request)
    {
        Gate::authorize("moderator");

        $query = Report::orderBy('created_at', 'desc');

        if (isset($request->status)) {
            $query->where('status', $request->status);
        }

        $query->paginate(10);
        $reports = $query->get();
        return ReportResource::collection($reports);
        //return response()->json($reports);
    }

    public function update(ChangeReportStatusRequest $changeReportStatusRequest)
    {
        Gate::authorize("moderator");

        $result = $this->report_service->changeStatus(request()->user(), $changeReportStatusRequest->report_id, $changeReportStatusRequest->status);
        return response()->json($result[0], $result[1]);
    }
}
