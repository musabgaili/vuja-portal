<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /** Full notifications page — the whole feed, and marks it seen on open. */
    public function index(NotificationService $notifications)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $items = $notifications->feed($user, 50);
        $notifications->markSeen($user);

        return view('notifications.index', ['items' => $items]);
    }

    public function seen(NotificationService $notifications)
    {
        $notifications->markSeen(Auth::user());

        return response()->json(['ok' => true]);
    }
}
