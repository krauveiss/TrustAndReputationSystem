<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/me', [AuthController::class, 'me']);
    Route::post('/report', [ReportController::class, 'store']);

    Route::prefix("moderation")->group(function () {
        Route::patch('change_report_status', [ReportController::class, 'update']);
    });
});
