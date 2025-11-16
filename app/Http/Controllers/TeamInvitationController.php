<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\TeamInvitationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TeamInvitationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index()
    {
        $teamMembers = User::where('type', 'internal')
            ->with('roles')
            ->latest()
            ->paginate(15);
            // return $teamMembers;

        return view('team.index', compact('teamMembers'));
    }

    public function create()
    {
        return view('team.invite');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'role' => 'required|in:employee,manager',
        ]);

        // Generate random password
        $password = Str::random(12);

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($password),
            'role' => $validated['role'],
            'type' => 'internal',
            'status' => 'active',
            'email_verified_at' => now(), // Auto-verify internal users
        ]);

        // Assign Spatie role
        $user->assignRole($validated['role']);

        // Send invitation email with credentials
        Mail::to($user->email)->send(new TeamInvitationMail($user, $password));

        return redirect()->route('team.index')
            ->with('success', "Team member invited! Credentials sent to {$user->email}");
    }

    public function destroy(User $user)
    {
        if ($user->type !== 'internal') {
            abort(403, 'Cannot delete client accounts from here.');
        }

        if ($user->id === Auth::id()) {
            abort(403, 'Cannot delete your own account.');
        }

        $user->delete();

        return back()->with('success', 'Team member removed successfully.');
    }
}
