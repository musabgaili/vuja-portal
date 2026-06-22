<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Lets a user choose which notification types they receive by EMAIL. */
class NotificationPreferenceController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        abort_unless($user, 403);

        return view('notifications.preferences', [
            'types' => $this->visibleTypes($user),
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $posted = (array) $request->input('prefs', []);
        $prefs = $user->notification_preferences ?? [];

        // Checkbox semantics: present in the payload = ON, absent = OFF. Only the
        // types this user can actually see are touched.
        foreach (array_keys($this->visibleTypes($user)) as $type) {
            $prefs[$type] = array_key_exists($type, $posted);
        }

        $user->update(['notification_preferences' => $prefs]);

        return back()->with('success', __('portal.notif_prefs.saved'));
    }

    /** Notification types relevant to this user, filtered by configured audience. */
    private function visibleTypes(User $user): array
    {
        return collect(config('notifications.types', []))
            ->filter(function ($cfg) use ($user) {
                return match ($cfg['audience'] ?? 'all') {
                    'manager' => $user->isManager(),
                    'internal' => $user->isInternal(),
                    default => true,
                };
            })
            ->all();
    }
}
