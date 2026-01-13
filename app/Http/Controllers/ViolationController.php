<?php

namespace App\Http\Controllers;

use App\Http\Requests\Violations\AddViolationRequest;
use App\Services\ViolationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ViolationController extends Controller
{
    public function __construct(protected ViolationService $violation_service) {}
    public function store(AddViolationRequest $addViolationRequest)
    {
        Gate::authorize('moderator');
        $result = $this->violation_service->addViolation(request()->user(), $addViolationRequest->user_id, $addViolationRequest->type, $addViolationRequest->severity);
        return response()->json($result[0], $result[1]);
    }
}
