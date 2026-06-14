<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function seen(NotificationService $notifications)
    {
        $notifications->markSeen(Auth::user());

        return response()->json(['ok' => true]);
    }
}
