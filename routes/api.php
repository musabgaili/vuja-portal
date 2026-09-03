<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\EngagementController;
use App\Http\Controllers\Api\V1\FcmTokenController;
use App\Http\Controllers\Api\V1\MeetingController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\NotificationPreferenceController;
use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API (v1) — token auth via Laravel Sanctum
|--------------------------------------------------------------------------
| One app, role-switched (internal staff + client). Public: login + social.
| Internal team routes are grouped under auth:sanctum + api.internal.
*/

Route::prefix('v1')->group(function () {
    // --- Public ---
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('auth/google', [AuthController::class, 'google'])->middleware('throttle:10,1');
    Route::post('auth/apple', [AuthController::class, 'apple'])->middleware('throttle:10,1');
    Route::get('auth/google/redirect', [AuthController::class, 'googleRedirect'])->middleware('throttle:10,1');
    Route::get('auth/google/callback', [AuthController::class, 'googleCallback'])->middleware('throttle:20,1');

    // --- Authenticated (any role) ---
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::patch('me', [AuthController::class, 'updateMe']);
        Route::put('me/password', [AuthController::class, 'updatePassword']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);

        Route::post('me/fcm-token', [FcmTokenController::class, 'store']);
        Route::delete('me/fcm-token', [FcmTokenController::class, 'destroy']);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::post('notifications/seen', [NotificationController::class, 'seen']);
        Route::get('notification-settings', [NotificationPreferenceController::class, 'show']);
        Route::put('notification-settings', [NotificationPreferenceController::class, 'update']);

        Route::get('engagement', [EngagementController::class, 'me']);

        Route::get('meetings', [MeetingController::class, 'index']);
        Route::get('meetings/{meeting}', [MeetingController::class, 'show']);

        // --- Internal team (manager, PM, employee) ---
        Route::middleware('api.internal')->group(function () {
            Route::get('dashboard', [DashboardController::class, 'index']);
            Route::get('activity-feed', [DashboardController::class, 'activity']);

            Route::get('my-tasks', [TaskController::class, 'myTasks']);
            Route::patch('staff-tasks/{staffTask}/status', [TaskController::class, 'updateStaffTask']);
            Route::patch('project-tasks/{projectTask}/status', [TaskController::class, 'updateProjectTask']);
        });
    });
});
