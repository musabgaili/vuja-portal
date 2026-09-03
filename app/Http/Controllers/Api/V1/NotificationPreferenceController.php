<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    /** Per-user email notification preferences (same keys as the web portal). */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $types = config('notifications.types', []);
        $prefs = $user->notification_preferences ?? [];

        $out = [];
        foreach ($types as $key => $meta) {
            $out[$key] = array_key_exists($key, $prefs)
                ? (bool) $prefs[$key]
                : (bool) ($meta['default'] ?? true);
        }

        return response()->json(['preferences' => $out]);
    }

    public function update(Request $request): JsonResponse
    {
        $types = array_keys(config('notifications.types', []));
        $rules = [];
        foreach ($types as $type) {
            $rules["preferences.$type"] = 'sometimes|boolean';
        }

        $data = $request->validate($rules);
        $incoming = $data['preferences'] ?? [];

        $user = $request->user();
        $merged = array_merge($user->notification_preferences ?? [], $incoming);
        $user->update(['notification_preferences' => $merged]);

        return $this->show($request);
    }
}
