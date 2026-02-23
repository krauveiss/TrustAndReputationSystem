<?php

namespace App\Http\Controllers;

use App\Exceptions\Violations\UserNotFoundException;
use App\Http\Requests\Admin\SetReputation;
use App\Models\User;
use App\Services\LogService;
use App\Services\ReputationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ReputationController extends Controller
{
    public function __construct(protected ReputationService $reputation_service, protected LogService $log_service) {}

    public function update(SetReputation $setReputation)
    {
        Gate::authorize('admin');

        try {
            $this->reputation_service->setScore($setReputation->user_id, $setReputation->reputation);
        } catch (UserNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            report($e);
            return response()->json(['message' => 'Something went wrong...'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->log_service->log($setReputation->user_id, request()->user(), "set reputation", 'new reputation: ' . $setReputation->reputation);
        return response()->json(['message' => 'Reputation changed'], Response::HTTP_OK);
    }
}
