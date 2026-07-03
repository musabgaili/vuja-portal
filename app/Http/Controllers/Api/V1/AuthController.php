<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Mobile API authentication (Laravel Sanctum personal access tokens).
 * One app, role-switched: the /me + login payloads carry the user's type/role
 * booleans so the client can branch its UI.
 */
class AuthController extends Controller
{
    /** Exchange email + password for a bearer token. */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            // Same generic error whether the email exists or not (no user enumeration).
            throw ValidationException::withMessages(['email' => [__('auth.failed')]]);
        }

        if (in_array($user->status, [UserStatus::SUSPENDED, UserStatus::INACTIVE], true)) {
            throw ValidationException::withMessages(['email' => ['This account is not active.']]);
        }

        $device = trim((string) ($data['device_name'] ?? '')) ?: 'mobile';
        $token = $user->createToken($device)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    /** The current authenticated user. */
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /** Revoke only the token used for this request (this device). */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out.']);
    }
}
