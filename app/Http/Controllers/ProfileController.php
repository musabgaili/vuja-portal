<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Show the user's profile page
     */
    public function show()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    /**
     * Show the profile edit form
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update user's basic profile information
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/'],
        ], [
            'phone.regex' => 'Phone number must contain only numbers (0-9).',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Update user's email address
     */
    public function updateEmail(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'required|string',
        ]);

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        // Update email
        $user->update([
            'email' => $validated['email'],
            'email_verified_at' => null, // Require re-verification
        ]);

        // Send verification email
        $user->sendEmailVerificationNotification();

        return back()->with('success', 'Email updated successfully! Please check your new email for verification.');
    }

    /**
     * Update user's password
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully!');
    }

    /**
     * Update user's phone number
     */
    public function updatePhone(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+$/'],
        ], [
            'phone.regex' => 'Phone number must contain only numbers (0-9).',
        ]);

        $user->update($validated);

        return back()->with('success', 'Phone number updated successfully!');
    }

    /**
     * Show security settings page
     */
    public function security()
    {
        $user = Auth::user();
        return view('profile.security', compact('user'));
    }

    /**
     * Delete user account
     */
    public function deleteAccount(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'password' => 'required|string',
            'confirmation' => 'required|in:DELETE',
        ]);

        // Verify password
        if (!Hash::check($validated['password'], $user->password)) {
            return back()->withErrors(['password' => 'The password is incorrect.']);
        }

        // Logout user
        Auth::logout();

        // Delete user account
        $user->delete();

        return redirect()->route('login')->with('success', 'Your account has been deleted successfully.');
    }
}
