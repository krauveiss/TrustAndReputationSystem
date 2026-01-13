<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ViolationController;
use App\Http\Middleware\CheckBan;
use App\Http\Middleware\CheckTimeout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);


Route::middleware(['auth:sanctum', CheckBan::class, CheckTimeout::class])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/report', [ReportController::class, 'store']);

    Route::prefix("moderation")->group(function () {
        Route::patch('change_report_status', [ReportController::class, 'update']);
        Route::post('violation', [ViolationController::class, 'store']);
    });
});
