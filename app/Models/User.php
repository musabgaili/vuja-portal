<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, InteractsWithMedia, LogsActivity, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'locale',
        'password',
        'role',
        'type',
        'status',
        'provider',
        'provider_id',
        'impact_points',
        'notification_preferences',
        'planner_defaults',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
            'impact_points' => 'integer',
            'notification_preferences' => 'array',
            'planner_defaults' => 'array',
            'locale' => 'string',
        ];
    }

    /** FCM device tokens registered by the Flutter (or web) client. */
    public function fcmTokens(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FcmToken::class);
    }

    /** Whether this user wants email for a given notification type (default per config). */
    public function wantsEmail(string $type): bool
    {
        $prefs = $this->notification_preferences ?? [];
        if (array_key_exists($type, $prefs)) {
            return (bool) $prefs[$type];
        }

        return (bool) config("notifications.types.$type.default", true);
    }

    /**
     * Hours this user's weekly plan must total — their committed capacity
     * (TeamMember.weekly_capacity_hours) when they're an engineer, otherwise the
     * global default. This is what ties the planner to the capacity floor.
     */
    public function plannerRequiredHours(): int
    {
        $member = $this->teamMember;
        $hours = ($member && $member->is_active)
            ? (int) round((float) $member->weekly_capacity_hours)
            : 0;

        return $hours > 0 ? $hours : (int) config('planner.required_hours', 40);
    }

    /**
     * Send the email-verification notification, but never let a mail-transport
     * failure (e.g. an unverified Resend domain) bubble up — that would 500 the
     * registration request and, with APP_DEBUG on, leak the request body. The
     * account is still created; the user can request a fresh link later.
     */
    public function sendEmailVerificationNotification(): void
    {
        try {
            parent::sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Engagement / Impact Points helpers.
     */
    public function engagementLogs()
    {
        return $this->hasMany(EngagementLog::class);
    }

    public function engagementLevel(): array
    {
        return app(\App\Services\EngagementService::class)->levelFor((int) $this->impact_points);
    }

    public function engagementProgress(): int
    {
        return app(\App\Services\EngagementService::class)->levelProgress((int) $this->impact_points);
    }

    /**
     * Client Engagement Points program (separate from the internal staff Impact
     * Points above): a spendable, ledger-backed loyalty balance. See spec §6.
     */
    public function pointsAccount(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PointsAccount::class, 'client_id');
    }

    /** Get (creating if needed) the client's points account — generates a referral code. */
    public function ensurePointsAccount(): PointsAccount
    {
        return $this->pointsAccount()->firstOrCreate([]);
    }

    /**
     * The client's pricing tier (student | entrepreneur | company), read from
     * their most recent quote; defaults to company. Drives tier-priced rewards.
     */
    public function clientTier(): string
    {
        return Quote::where('client_id', $this->id)->latest('id')->value('customer_category') ?: 'company';
    }

    /**
     * Performance Targets — monthly KPI goals the manager sets for internal staff.
     */
    public function performanceTargets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PerformanceTarget::class, 'user_id');
    }

    /** Staff who hold performance targets: internal employees and project managers (not managers/clients). */
    public function holdsTargets(): bool
    {
        return $this->isInternal() && ($this->isEmployee() || $this->isProjectManager());
    }

    /** Capacity layer: the engineer (team member) record for this user, if tracked. */
    public function teamMember(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TeamMember::class, 'user_id');
    }

    /**
     * Get the activity log options for the model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'phone', 'role', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->status === UserStatus::ACTIVE;
    }

    /**
     * Check if user is a client.
     */
    public function isClient(): bool
    {
        return $this->role === UserRole::CLIENT;
    }

    /**
     * Check if user is an employee.
     */
    public function isEmployee(): bool
    {
        return $this->role === UserRole::EMPLOYEE;
    }

    /**
     * Check if user is a manager.
     */
    public function isManager(): bool
    {
        return $this->role === UserRole::MANAGER;
    }

    /**
     * Check if user has the global project manager role.
     */
    public function isProjectManager(): bool
    {
        return $this->role === UserRole::PROJECT_MANAGER;
    }

    /**
     * May this user create/run projects directly? Managers and project managers
     * both can (a PM can already approve a proposal into a live project, so they
     * may also create one outright). Excludes bulk import + delete, which stay
     * manager-only.
     */
    public function canManageProjects(): bool
    {
        return $this->isManager() || $this->isProjectManager();
    }

    /**
     * Whether this user may use client-facing project routes (aligned with non-internal dashboard users).
     */
    public function canUseClientProjectPortal(): bool
    {
        return ! $this->isInternal();
    }

    /**
     * Check if user is internal staff.
     */
    public function isInternal(): bool
    {
        return $this->type === 'internal';
    }

    /**
     * Check if user is a project manager for a specific project.
     */
    public function isProjectManagerOf(Project $project): bool
    {
        return $project->project_manager_id === $this->id;
    }

    /**
     * Check if user is part of a project team.
     */
    public function isProjectMemberOf(Project $project): bool
    {
        return $project->projectPeople()->where('user_id', $this->id)->exists();
    }

    /** Internal chat channels & DMs this user belongs to. */
    public function chatChannels(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ChatChannel::class, 'chat_channel_user')
            ->withPivot(['role', 'last_read_message_id', 'muted', 'joined_at'])
            ->withTimestamps();
    }

    /** Unread @mention count (bell badge + Mentions inbox). */
    public function unreadChatMentionsCount(): int
    {
        return ChatMessageMention::where('user_id', $this->id)->whereNull('read_at')->count();
    }

    /**
     * Check if user is internal (employee, manager, or project manager).
     */
    // public function isInternal(): bool
    // {
    //     return in_array($this->role, [
    //         UserRole::EMPLOYEE,
    //         UserRole::MANAGER,
    //         UserRole::PROJECT_MANAGER
    //     ]);
    // }

    /**
     * Get the user's avatar from media library.
     */
    public function getAvatarAttribute(): ?string
    {
        return $this->getFirstMediaUrl('avatar');
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::updated(function (User $user) {
            if ($user->wasChanged('email_verified_at') && $user->email_verified_at !== null && $user->status !== UserStatus::ACTIVE) {
                $user->status = UserStatus::ACTIVE;
                $user->saveQuietly(); // Use saveQuietly to avoid triggering the event again
            }
        });
    }

    /**
     * Avatar initials: first letter of the first and last name parts (e.g.
     * "John Client" -> "JC"). Falls back to the first two letters of a single
     * word, or "?" when the name is empty.
     */
    public function initials(): string
    {
        $parts = array_values(array_filter(preg_split('/\s+/', trim((string) $this->name)) ?: []));

        if (count($parts) === 0) {
            return '?';
        }

        if (count($parts) === 1) {
            return mb_strtoupper(mb_substr($parts[0], 0, 2));
        }

        return mb_strtoupper(mb_substr($parts[0], 0, 1).mb_substr(end($parts), 0, 1));
    }
}
