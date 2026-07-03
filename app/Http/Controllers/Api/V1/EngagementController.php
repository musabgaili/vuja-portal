<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Targets\ActualsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Gamification snapshot for the app home: impact points, level, and (staff) target attainment. */
class EngagementController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $level = $user->engagementLevel();

        $out = [
            'impact_points' => (int) $user->impact_points,
            'level_index' => $level['index'] ?? null,
            'progress' => $user->engagementProgress(),
        ];

        // Internal staff who hold monthly targets also get their overall attainment %.
        if ($user->isInternal() && $user->holdsTargets()) {
            $attainment = app(ActualsService::class)->overallAttainment($user, now()->startOfMonth());
            $out['targets_attainment'] = $attainment === null ? null : (int) round($attainment);
        }

        return response()->json($out);
    }
}
