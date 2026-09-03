<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Api\MobileDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private MobileDashboardService $dashboard) {}

    /** Role-aware home summary cards for the Flutter app. */
    public function index(Request $request): JsonResponse
    {
        return response()->json($this->dashboard->summary($request->user()));
    }

    /** Recent activity feed (internal staff). */
    public function activity(Request $request): JsonResponse
    {
        $limit = min(50, max(1, (int) $request->integer('limit', 20)));

        return response()->json([
            'items' => $this->dashboard->activityFeed($request->user(), $limit),
        ]);
    }
}
