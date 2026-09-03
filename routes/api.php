<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\EngagementController;
use App\Http\Controllers\Api\V1\FcmTokenController;
use App\Http\Controllers\Api\V1\MeetingController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\NotificationPreferenceController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ProjectMilestoneController;
use App\Http\Controllers\Api\V1\ProjectTaskController;
use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API (v1) — token auth via Laravel Sanctum
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('auth/google', [AuthController::class, 'google'])->middleware('throttle:10,1');
    Route::post('auth/apple', [AuthController::class, 'apple'])->middleware('throttle:10,1');
    Route::get('auth/google/redirect', [AuthController::class, 'googleRedirect'])->middleware('throttle:10,1');
    Route::get('auth/google/callback', [AuthController::class, 'googleCallback'])->middleware('throttle:20,1');

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

        Route::middleware('api.internal')->group(function () {
            Route::get('dashboard', [DashboardController::class, 'index']);
            Route::get('activity-feed', [DashboardController::class, 'activity']);

            Route::get('my-tasks', [TaskController::class, 'myTasks']);
            Route::patch('staff-tasks/{staffTask}/status', [TaskController::class, 'updateStaffTask']);
            Route::patch('project-tasks/{projectTask}/status', [TaskController::class, 'updateProjectTask']);

            // Sprint 3 — Projects
            Route::get('projects', [ProjectController::class, 'index']);
            Route::post('projects', [ProjectController::class, 'store']);
            Route::get('projects/{project}', [ProjectController::class, 'show']);
            Route::patch('projects/{project}', [ProjectController::class, 'update']);
            Route::post('projects/{project}/close', [ProjectController::class, 'close']);
            Route::get('projects/{project}/comments', [ProjectController::class, 'comments']);
            Route::post('projects/{project}/comments', [ProjectController::class, 'addComment']);

            Route::get('projects/{project}/milestones', [ProjectMilestoneController::class, 'index']);
            Route::post('projects/{project}/milestones', [ProjectMilestoneController::class, 'store']);
            Route::patch('milestones/{milestone}', [ProjectMilestoneController::class, 'update']);

            Route::get('projects/{project}/tasks', [ProjectTaskController::class, 'index']);
            Route::get('projects/{project}/tasks/kanban', [ProjectTaskController::class, 'kanban']);
            Route::post('projects/{project}/tasks', [ProjectTaskController::class, 'store']);
            Route::patch('projects/tasks/{projectTask}', [ProjectTaskController::class, 'update']);
            Route::delete('projects/tasks/{projectTask}', [ProjectTaskController::class, 'destroy']);

            // Sprint 4 — Chat
            Route::get('chat/channels', [ChatController::class, 'channels']);
            Route::post('chat/channels', [ChatController::class, 'storeChannel']);
            Route::post('chat/dm', [ChatController::class, 'startDm']);
            Route::get('chat/browse', [ChatController::class, 'browse']);
            Route::post('chat/channels/{channel}/join', [ChatController::class, 'requestJoin']);
            Route::get('chat/channels/{channel}/messages', [ChatController::class, 'messages']);
            Route::post('chat/channels/{channel}/messages', [ChatController::class, 'storeMessage']);
            Route::patch('chat/messages/{message}', [ChatController::class, 'updateMessage']);
            Route::delete('chat/messages/{message}', [ChatController::class, 'destroyMessage']);
            Route::post('chat/messages/{message}/react', [ChatController::class, 'react']);
            Route::get('chat/channels/{channel}/thread/{message}', [ChatController::class, 'thread']);
            Route::get('chat/mentions', [ChatController::class, 'mentions']);
            Route::post('chat/mentions/read-all', [ChatController::class, 'readAllMentions']);
            Route::get('chat/members', [ChatController::class, 'members']);
            Route::get('chat/attachments/{attachment}', [ChatController::class, 'downloadAttachment']);
        });
    });
});
