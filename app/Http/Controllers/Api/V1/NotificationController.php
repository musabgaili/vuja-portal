<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Support\MobileDeepLink;
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

        $items = collect($this->notifications->feed($user, $limit))
            ->map(fn (array $item) => $this->present($item))
            ->values()
            ->all();

        return response()->json([
            'unread_count' => $this->notifications->unreadCount($user),
            'items' => $items,
        ]);
    }

    /** Mark the feed as seen (clears the unread badge). */
    public function seen(Request $request): JsonResponse
    {
        $this->notifications->markSeen($request->user());

        return response()->json(['ok' => true]);
    }

    /** @param  array<string, mixed>  $item */
    private function present(array $item): array
    {
        $url = (string) ($item['url'] ?? '');
        $path = MobileDeepLink::fromUrl($url);

        return [
            'id' => hash('sha256', $url.'|'.($item['at'] ?? '').'|'.($item['text'] ?? '')),
            'icon' => $item['icon'] ?? null,
            'text' => $item['text'] ?? '',
            'at' => (int) ($item['at'] ?? 0),
            'ago' => $item['ago'] ?? '',
            'url' => $url !== '' ? $url : null,
            'deep_link' => $path ? MobileDeepLink::absolute($path) : null,
        ];
    }
}
