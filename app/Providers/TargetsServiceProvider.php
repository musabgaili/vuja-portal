<?php

namespace App\Providers;

use App\Models\User;
use App\Targets\Adapters\LeaveEntriesBridge;
use App\Targets\Adapters\ProjectAssignmentsBridge;
use App\Targets\Adapters\ScopeQuotesBridge;
use App\Targets\Adapters\UserStaffDirectory;
use App\Targets\Contracts\LeaveBridge;
use App\Targets\Contracts\ProjectsBridge;
use App\Targets\Contracts\QuotesBridge;
use App\Targets\Contracts\StaffDirectory;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/** Wires the Targets & Capacity layer to the existing models (spec §5). */
class TargetsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(StaffDirectory::class, UserStaffDirectory::class);
        $this->app->bind(ProjectsBridge::class, ProjectAssignmentsBridge::class);
        $this->app->bind(QuotesBridge::class, ScopeQuotesBridge::class);
        $this->app->bind(LeaveBridge::class, LeaveEntriesBridge::class);
    }

    public function boot(): void
    {
        // Managers configure targets/capacity, assign projects, view all dashboards.
        Gate::define('manage-targets', fn (User $u) => $u->isManager());
    }
}
