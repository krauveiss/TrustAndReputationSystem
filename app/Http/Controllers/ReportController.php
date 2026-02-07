<?php

namespace App\Http\Controllers;

use App\Exceptions\Report\ReportFloodException;
use App\Exceptions\Report\ReportNotFoundException;
use App\Exceptions\Report\SelfReportException;
use App\Exceptions\Report\WrongUserException;
use App\Http\Requests\GetReportsRequest;
use App\Http\Requests\Reports\ChangeReportStatusRequest;
use App\Http\Requests\Reports\ReportRequest;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Services\ReportService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function __construct(private ReportService $report_service) {}

    public function store(ReportRequest $reportRequest)
    {
        try {
            $this->report_service->sendReport(request()->user(), $reportRequest->target_name, $reportRequest->reason);
        } catch (WrongUserException | SelfReportException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (ReportFloodException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            report($e);
            return response()->json(['message' => 'Something went wrong...'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json(['message' => 'Report recorded'], Response::HTTP_OK);
    }

    public function index(GetReportsRequest $request)
    {
        Gate::authorize("moderator");

        $query = Report::orderByRaw("status='pending' DESC")->orderBy('created_at', 'asc');

        if (isset($request->status)) {
            $query->where('status', $request->status);
        }

        $query->paginate(10);
        $reports = $query->get();
        return ReportResource::collection($reports);
    }

    public function update(ChangeReportStatusRequest $changeReportStatusRequest)
    {
        Gate::authorize("moderator");

        try {
            $this->report_service->changeStatus(request()->user(), $changeReportStatusRequest->report_id, $changeReportStatusRequest->status);
        } catch (ReportNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            report($e);
            return response()->json(['message' => 'Something went wrong...'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json(['message' => 'The complaint has been updated'], Response::HTTP_OK);
    }
}
