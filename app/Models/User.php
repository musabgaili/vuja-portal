<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, InteractsWithMedia, LogsActivity, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'type',
        'status',
        'provider',
        'provider_id',
        'impact_points',
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
        ];
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
}
