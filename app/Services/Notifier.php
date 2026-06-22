<?php

namespace App\Services;

use App\Mail\GenericNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Central email-notification dispatcher. The in-app bell (NotificationService) is
 * a derived feed and always shows everything; this adds the EMAIL channel, gated
 * by each user's per-type preference. A mail failure is logged, never thrown, so
 * it can't break the action that triggered it.
 */
class Notifier
{
    /**
     * Email one user about a notification, if they have that type enabled.
     *
     * @param  string  $type     a key from config('notifications.types')
     * @param  string  $subject  email subject line
     * @param  string  $heading  bold heading inside the email
     * @param  string  $body     the message body (plain text; newlines preserved)
     */
    public function email(?User $user, string $type, string $subject, string $heading, string $body, ?string $url = null, ?string $actionText = null): void
    {
        if (! $user || ! filled($user->email) || ! $user->wantsEmail($type)) {
            return;
        }

        try {
            $locale = app()->getLocale();
            Mail::to($user->email)->send(
                new GenericNotification($subject, $heading, $body, $url, $actionText, $locale)
            );
        } catch (\Throwable $e) {
            Log::warning('Notification email failed ('.$type.' to user '.$user->id.'): '.$e->getMessage());
        }
    }

    /** Email several users about the same notification. */
    public function emailMany(iterable $users, string $type, string $subject, string $heading, string $body, ?string $url = null, ?string $actionText = null): void
    {
        foreach ($users as $user) {
            $this->email($user, $type, $subject, $heading, $body, $url, $actionText);
        }
    }
}
