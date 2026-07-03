<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Notification feed for the mobile app (derived from the same NotificationService as the web bell). */
class NotificationController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    /** The user's notification feed + unread count. */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = min(50, max(1, (int) $request->integer('limit', 20)));

        return response()->json([
            'unread_count' => $this->notifications->unreadCount($user),
            'items' => $this->notifications->feed($user, $limit),
        ]);
    }

    /** Mark the feed as seen (clears the unread badge). */
    public function seen(Request $request): JsonResponse
    {
        $this->notifications->markSeen($request->user());

        return response()->json(['ok' => true]);
    }
}
