<?php

namespace App\Console\Commands;

use App\Models\Meeting;
use App\Models\User;
use App\Services\Notifier;
use Illuminate\Console\Command;

/**
 * Emails attendees a reminder shortly before their meeting starts. Each meeting
 * is reminded once (guarded by meetings.reminded_at).
 */
class SendMeetingReminders extends Command
{
    protected $signature = 'meetings:send-reminders';

    protected $description = 'Email attendees a reminder shortly before their meeting.';

    public function handle(Notifier $notifier): int
    {
        $now = now();
        $window = $now->copy()->addMinutes((int) config('meetings.reminder_lead_minutes', 60));

        $meetings = Meeting::with(['attendees', 'teamMember', 'client'])
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->whereNull('reminded_at')
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$now, $window])
            ->get();

        foreach ($meetings as $m) {
            $when = $m->scheduled_at->translatedFormat('l, M j, Y g:i A');

            $recipientIds = collect([$m->team_member_id, $m->client_id])
                ->merge($m->attendees->where('status', 'accepted')->pluck('user_id'))
                ->filter()->unique();

            foreach ($recipientIds as $uid) {
                $user = User::find($uid);
                if (! $user || ! $user->isInternal()) {
                    continue;
                }
                $notifier->email($user, 'meeting_reminder',
                    __('portal.notif_prefs.mail.meeting_reminder_subject'),
                    __('portal.notif_prefs.mail.meeting_reminder_heading'),
                    __('portal.notif_prefs.mail.meeting_reminder_body', ['title' => $m->title, 'when' => $when]),
                    route('meetings.internal.my-meetings'), __('portal.meetings.my_meetings'));
            }

            $m->update(['reminded_at' => $now]);
        }

        $this->info('Sent reminders for '.$meetings->count().' meeting(s).');

        return self::SUCCESS;
    }
}
