<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Fcm\FcmTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    public function __construct(private FcmTokenService $fcm) {}

    /** Register or refresh this device's FCM token. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => 'required|string|max:512',
            'device' => 'nullable|string|max:255',
            'platform' => 'nullable|in:ios,android,web',
        ]);

        $record = $this->fcm->register(
            $request->user(),
            $data['token'],
            $data['device'] ?? null,
            $data['platform'] ?? null,
        );

        return response()->json([
            'id' => $record->id,
            'token' => $record->token,
            'device' => $record->device,
            'platform' => $record->platform,
        ], 201);
    }

    /** Unregister this device's FCM token (call on logout). */
    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => 'required|string|max:512',
        ]);

        $this->fcm->unregister($request->user(), $data['token']);

        return response()->json(['message' => 'FCM token removed.']);
    }
}
