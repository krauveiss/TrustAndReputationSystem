<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\SetReputation;
use App\Models\User;
use App\Services\ReputationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReputationController extends Controller
{
    public function __construct(protected ReputationService $reputation_service) {}

    public function update(SetReputation $setReputation)
    {
        Gate::authorize('admin');
        $user = User::find($setReputation->user_id);
        $result = $this->reputation_service->setScore($user, $setReputation->reputation);
        return response()->json($result[0], $result[1]);
    }
}
