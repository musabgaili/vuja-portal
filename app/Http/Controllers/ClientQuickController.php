<?php

namespace App\Http\Controllers;

use App\Exceptions\ClientEmailTakenException;
use App\Services\Clients\ClientProvisioningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Lightweight "add a new client" used by the inline modal on the project and
 * opportunity forms. Creates (or reuses) a selectable client account via
 * ClientProvisioningService and optionally emails an activation invitation.
 */
class ClientQuickController extends Controller
{
    public function __construct(private ClientProvisioningService $provisioning) {}

    public function store(Request $request): JsonResponse
    {
        abort_unless(Auth::user()?->isInternal(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:160',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:40',
            'company' => 'nullable|string|max:160',
            'invite' => 'nullable|boolean',
        ]);

        try {
            $result = $this->provisioning->findOrCreateClient($data, Auth::id());
        } catch (ClientEmailTakenException $e) {
            return response()->json(['message' => __('portal.quick_client.email_taken')], 422);
        }

        $invited = false;
        if ($request->boolean('invite')) {
            $invited = $this->provisioning->sendInvitation($result['user'], Auth::user());
        }

        return response()->json([
            'id' => $result['user']->id,
            'name' => $result['user']->name,
            'email' => $result['user']->email,
            'reused' => ! $result['created'],
            'invited' => $invited,
        ]);
    }
}
