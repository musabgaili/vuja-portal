<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Client\ClientRequestsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Client "My Requests" — one unified, paginated, filterable feed across the
 * service-line models. Shares ClientRequestsService with the web page.
 */
class RequestController extends Controller
{
    public function __construct(private ClientRequestsService $requests) {}

    /** GET /api/v1/requests?status=&type=&page= — the unified feed + summary. */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->isClient(), 403);

        $data = $this->requests->build(
            $user->id,
            $request->query('status'),
            $request->query('type'),
        );
        $paginator = $data['requests'];

        return response()->json([
            'items' => $this->requests->apiItems($paginator->getCollection()),
            'summary' => $data['summary'],
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'has_more' => $paginator->hasMorePages(),
            ],
        ]);
    }
}
