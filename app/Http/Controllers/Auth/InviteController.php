<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Handles client invitation acceptance: a signed link (emailed by
 * ClientProvisioningService) lets an invited client set their password and
 * activate their account in one step.
 */
class InviteController extends Controller
{
    public function show(Request $request, User $user)
    {
        abort_unless($user->isClient(), 403);

        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.accept-invite', [
            'invitee' => $user,
            // The POST validates against the same signature on this URL.
            'actionUrl' => $request->fullUrl(),
        ]);
    }

    public function store(Request $request, User $user)
    {
        abort_unless($user->isClient(), 403);

        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user->forceFill([
            'password' => Hash::make($request->input('password')),
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', __('portal.invite.welcome'));
    }
}
