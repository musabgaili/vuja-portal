<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EngagementController;
use App\Http\Controllers\Api\V1\FcmTokenController;
use App\Http\Controllers\Api\V1\MeetingController;
use App\Http\Controllers\Api\V1\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API (v1) — token auth via Laravel Sanctum
|--------------------------------------------------------------------------
| One app, role-switched (internal staff + client). Public: login + social.
| Everything else requires a bearer token (auth:sanctum). Grouped under /api/v1.
*/

Route::prefix('v1')->group(function () {
    // --- Public ---
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('auth/google', [AuthController::class, 'google'])->middleware('throttle:10,1');
    Route::post('auth/apple', [AuthController::class, 'apple'])->middleware('throttle:10,1');
    Route::get('auth/google/redirect', [AuthController::class, 'googleRedirect'])->middleware('throttle:10,1');
    Route::get('auth/google/callback', [AuthController::class, 'googleCallback'])->middleware('throttle:20,1');

    // --- Authenticated ---
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::patch('me', [AuthController::class, 'updateMe']);
        Route::put('me/password', [AuthController::class, 'updatePassword']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);

        Route::post('me/fcm-token', [FcmTokenController::class, 'store']);
        Route::delete('me/fcm-token', [FcmTokenController::class, 'destroy']);

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
