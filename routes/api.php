<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EngagementController;
use App\Http\Controllers\Api\V1\MeetingController;
use App\Http\Controllers\Api\V1\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API (v1) — token auth via Laravel Sanctum
|--------------------------------------------------------------------------
| One app, role-switched (internal staff + client). Public: login. Everything
| else requires a bearer token (auth:sanctum). Grouped under /api/v1.
*/

Route::prefix('v1')->group(function () {
    // --- Public ---
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    // --- Authenticated ---
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);

        // Notifications
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::post('notifications/seen', [NotificationController::class, 'seen']);

        // Engagement / targets snapshot
        Route::get('engagement', [EngagementController::class, 'me']);

        // Meetings (read)
        Route::get('meetings', [MeetingController::class, 'index']);
        Route::get('meetings/{meeting}', [MeetingController::class, 'show']);
    });
});
