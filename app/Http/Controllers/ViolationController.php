<?php

namespace App\Http\Controllers;

use App\Exceptions\Violations\SuspiciousActivityException;
use App\Exceptions\Violations\UserNotFoundException;
use App\Exceptions\Violations\ViolationNotFoundException;
use App\Http\Requests\Violations\AddViolationRequest;
use App\Http\Requests\Violations\ChangeViolationStatus;
use App\Services\ViolationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ViolationController extends Controller
{
    public function __construct(protected ViolationService $violation_service) {}
    public function store(AddViolationRequest $addViolationRequest)
    {
        Gate::authorize('moderator');
        try {
            $this->violation_service->addViolation(request()->user(), $addViolationRequest->user_id, $addViolationRequest->type, $addViolationRequest->severity);
        } catch (UserNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (SuspiciousActivityException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            report($e);
            return response()->json(['message' => 'Something went wrong...'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json(['message' => 'Violation recorded, reputation changed.'], Response::HTTP_OK);
    }

    public function update(ChangeViolationStatus $changeViolationStatus)
    {
        Gate::authorize('admin');
        try {
            $this->violation_service->changeViolationStatus($changeViolationStatus->violation_id, $changeViolationStatus->status, $changeViolationStatus->comment);
        } catch (ViolationNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            report($e);
            return response()->json(['message' => 'Something went wrong...'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json(['message' => 'Violation recorded, reputation changed.'], Response::HTTP_OK);
    }
}
