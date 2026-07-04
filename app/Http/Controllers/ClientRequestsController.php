<?php

namespace App\Http\Controllers;

use App\Services\Client\ClientRequestsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientRequestsController extends Controller
{
    public function __construct(private ClientRequestsService $requests) {}

    /**
     * Display all client requests across all services. The aggregation now lives
     * in ClientRequestsService (shared with the mobile API); this stays a thin
     * controller. Behaviour is unchanged — guarded by ClientRequestsWebTest.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->isClient(), 403);

        $statusFilter = $request->get('status');
        $typeFilter = $request->get('type');

        $data = $this->requests->build($user->id, $statusFilter, $typeFilter);

        return view('client.requests', [
            'allRequests' => $data['requests'],
            'summary' => $data['summary'],
            'statusFilter' => $statusFilter,
            'typeFilter' => $typeFilter,
        ]);
    }
}
