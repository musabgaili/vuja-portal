<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\ClientDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Mobile home-screen data. Uses the same ClientDashboardService as the web
 * client dashboard so the headline numbers match exactly.
 */
class DashboardController extends Controller
{
    public function __construct(private readonly ClientDashboardService $dashboard) {}

    /** GET /api/v1/dashboard — client home summary (scoped to the caller). */
    public function client(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'stats' => $this->dashboard->stats($user),
            'recent_activity' => $this->dashboard->recentActivity($user),
            'active_projects' => $this->dashboard->activeProjects($user),
        ]);
    }
}
