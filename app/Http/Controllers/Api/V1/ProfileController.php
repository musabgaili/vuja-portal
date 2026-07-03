<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Mobile profile management. Mirrors the web ProfileController validation
 * rules exactly; returns JSON (422 field errors map to the same field names
 * the mobile form uses). The web controller is unchanged.
 */
class ProfileController extends Controller
{
    /** GET /api/v1/profile — the authenticated user. */
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /** PUT /api/v1/profile — update name and phone. */
    public function update(Request $request): UserResource
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/'],
        ], [
            'phone.regex' => __('Phone number must contain only numbers (0-9).'),
        ]);

        $request->user()->update($validated);

        return new UserResource($request->user()->fresh());
    }

    /** PUT /api/v1/profile/email — change email (re-verification required). */
    public function updateEmail(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => ['required', 'string', 'current_password:sanctum'],
        ]);

        // Set directly (not mass-assign): email_verified_at is not fillable, so
        // ->update() would silently drop the null and leave the account "verified".
        $user->forceFill([
            'email' => $validated['email'],
            'email_verified_at' => null,
        ])->save();
        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => __('Email updated successfully! Please check your new email for verification.'),
            'user' => new UserResource($user->fresh()),
        ]);
    }

    /** PUT /api/v1/profile/password — change password (current password required). */
    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string', 'current_password:sanctum'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update(['password' => Hash::make($validated['password'])]);

        return response()->json(['message' => __('Password updated successfully!')]);
    }

    /** PUT /api/v1/profile/phone — update phone only. */
    public function updatePhone(Request $request): UserResource
    {
        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/'],
        ], [
            'phone.regex' => __('Phone number must contain only numbers (0-9).'),
        ]);

        $request->user()->update($validated);

        return new UserResource($request->user()->fresh());
    }

    /** DELETE /api/v1/profile — delete own account (password + DELETE confirmation). */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'current_password:sanctum'],
            'confirmation' => ['required', 'in:DELETE'],
        ]);

        $user = $request->user();
        $user->tokens()->delete();   // revoke all mobile tokens
        $user->delete();

        return response()->json(['message' => __('Your account has been deleted successfully.')]);
    }
}
