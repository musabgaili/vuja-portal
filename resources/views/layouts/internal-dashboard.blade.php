<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('portal.dashboard')) - {{ __('portal.platform_name') }}</title>
    @include('partials.theme-head')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="internal-dashboard">
    <!-- Mobile Menu Toggle -->
    <button type="button" class="mobile-menu-toggle" onclick="toggleSidebar()" aria-label="{{ __('portal.app.toggle_nav') }}">
        <i class="fas fa-bars" aria-hidden="true"></i>
    </button>
    
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    @include('partials.ip-toasts')
    
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                @include('partials.brand')
            </div>

            <!-- Sidebar Navigation -->
            <nav class="sidebar-nav">
                <!-- Dashboard -->
                <div class="nav-section">
                    <a href="{{ route('internal.dashboard') }}" class="nav-item">
                        <i class="fas fa-home"></i>
                        {{ __('portal.nav.dashboard') }}
                    </a>
                    @if(auth()->user()->isManager() || auth()->user()->isProjectManager())
                        @php $approvalCount = app(\App\Services\ApprovalService::class)->count(auth()->user()); @endphp
                        <a href="{{ route('approvals.index') }}" class="nav-item d-flex align-items-center {{ request()->routeIs('approvals.*') ? 'active' : '' }}">
                            <span><i class="fas fa-clipboard-check"></i> {{ __('portal.nav.approvals') }}</span>
                            @if($approvalCount > 0)<span class="badge bg-danger ms-auto">{{ $approvalCount }}</span>@endif
                        </a>
                    @endif
                    @if(auth()->user()->isManager())
                    <a href="{{ route('control-tower.index') }}" class="nav-item {{ request()->routeIs('control-tower.*') ? 'active' : '' }}">
                        <i class="fas fa-tower-observation"></i>
                        {{ __('portal.nav.control_tower') }}
                    </a>
                    <a href="{{ route('workload.index') }}" class="nav-item {{ request()->routeIs('workload.*') ? 'active' : '' }}">
                        <i class="fas fa-fire"></i>
                        {{ __('portal.nav.workload') }}
                    </a>
                    @endif
                    <a href="{{ route('engagement.index') }}" class="nav-item {{ request()->routeIs('engagement.index') || request()->routeIs('engagement.thank-you') ? 'active' : '' }}">
                        <i class="fas fa-bolt"></i>
                        {{ __('portal.nav.engagement') }}
                    </a>
                    <a href="{{ route('targets.my') }}" class="nav-item {{ request()->routeIs('targets.my') ? 'active' : '' }}">
                        <i class="fas fa-bullseye"></i>
                        {{ __('targets.my_title') }}
                    </a>
                    @if(auth()->user()->teamMember)
                    <a href="{{ route('capacity.my') }}" class="nav-item {{ request()->routeIs('capacity.my') ? 'active' : '' }}">
                        <i class="fas fa-gauge-high"></i>
                        {{ __('targets.cap.my_title') }}
                    </a>
                    @endif
                    <a href="{{ route('improvement-ideas.index') }}" class="nav-item {{ request()->routeIs('improvement-ideas.index') || request()->routeIs('improvement-ideas.create') || request()->routeIs('improvement-ideas.show') ? 'active' : '' }}">
                        <i class="fas fa-rocket"></i>
                        {{ __('portal.nav.improvement_ideas') }}
                    </a>
                    <a href="{{ route('weekly-planner.index') }}" class="nav-item {{ request()->routeIs('weekly-planner.index') ? 'active' : '' }}">
                        <i class="fas fa-calendar-week"></i>
                        {{ __('portal.nav.weekly_planner') }}
                    </a>
                    @if(!auth()->user()->isManager())
                    <a href="{{ route('weekly-planner.presence') }}" class="nav-item {{ request()->routeIs('weekly-planner.presence') || request()->routeIs('weekly-planner.show') ? 'active' : '' }}">
                        <i class="fas fa-map-marker-alt"></i>
                        {{ __('portal.planner.team_presence') }}
                    </a>
                    @endif
                    <a href="{{ route('staff-tasks.index') }}" class="nav-item {{ request()->routeIs('staff-tasks.*') ? 'active' : '' }}">
                        <i class="fas fa-list-check"></i>
                        {{ auth()->user()->isManager() ? __('portal.nav.staff_tasks') : __('portal.nav.my_tasks') }}
                    </a>
                    <a href="{{ route('spend.index') }}" class="nav-item {{ request()->routeIs('spend.*') ? 'active' : '' }}">
                        <i class="fas fa-receipt"></i>
                        {{ __('portal.nav.spend') }}
                    </a>
                    @if(auth()->user()->isManager())
                    <a href="{{ route('weekly-planner.review') }}" class="nav-item {{ request()->routeIs('weekly-planner.review') || request()->routeIs('weekly-planner.presence') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-check"></i>
                        {{ __('portal.nav.plans_review') }}
                    </a>
                    @endif
                </div>

                <!-- Sales / CRM -->
                <div class="nav-section">
                    <div class="nav-section-title">{{ __('portal.nav.sales') }}</div>
                    <a href="{{ route('crm.index') }}" class="nav-item {{ request()->routeIs('crm.*') ? 'active' : '' }}">
                        <i class="fas fa-funnel-dollar"></i>
                        {{ __('portal.nav.pipeline') }}
                    </a>
                    <a href="{{ route('companies.index') }}" class="nav-item {{ request()->routeIs('companies.*') ? 'active' : '' }}">
                        <i class="fas fa-building"></i>
                        {{ __('portal.nav.companies') }}
                    </a>
                    <a href="{{ route('contacts.index') }}" class="nav-item {{ request()->routeIs('contacts.*') ? 'active' : '' }}">
                        <i class="fas fa-address-book"></i>
                        {{ __('portal.nav.contacts') }}
                    </a>
                    <a href="{{ route('crm-activities.index') }}" class="nav-item {{ request()->routeIs('crm-activities.*') ? 'active' : '' }}">
                        <i class="fas fa-list-check"></i>
                        {{ __('portal.nav.activities') }}
                    </a>
                    <a href="{{ route('quotes.index') }}" class="nav-item {{ request()->routeIs('quotes.index') || request()->routeIs('quotes.show') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice"></i>
                        {{ __('portal.nav.quotes') }}
                    </a>
                    @if(auth()->user()->isManager())
                    <a href="{{ route('crm-reports.index') }}" class="nav-item {{ request()->routeIs('crm-reports.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i>
                        {{ __('portal.nav.sales_reports') }}
                    </a>
                    <a href="{{ route('crm-stages.index') }}" class="nav-item {{ request()->routeIs('crm-stages.*') ? 'active' : '' }}">
                        <i class="fas fa-sliders"></i>
                        {{ __('portal.nav.sales_stages') }}
                    </a>
                    @endif
                </div>

                <!-- Service Requests -->
                <div class="nav-section">
                    <div class="nav-section-title">{{ __('portal.nav.services') }}</div>
                    <a href="{{ route('ideas.manager.index') }}" class="nav-item">
                        <i class="fas fa-lightbulb"></i>
                        {{ __('portal.nav.ideas') }}
                    </a>
                    <a href="{{ route('consultations.manager.index') }}" class="nav-item">
                        <i class="fas fa-comments"></i>
                        {{ __('portal.nav.consultations') }}
                    </a>
                    <a href="{{ route('research.manager.index') }}" class="nav-item">
                        <i class="fas fa-search"></i>
                        {{ __('portal.nav.research') }}
                    </a>
                    <a href="{{ route('ip.manager.index') }}" class="nav-item">
                        <i class="fas fa-file-contract"></i>
                        {{ __('portal.nav.ip_reg') }}
                    </a>
                    <a href="{{ route('copyright.manager.index') }}" class="nav-item">
                        <i class="fas fa-copyright"></i>
                        {{ __('portal.nav.copyright') }}
                    </a>
                    <a href="{{ route('prototypes.manager.index') }}" class="nav-item {{ request()->routeIs('prototypes.manager.*') ? 'active' : '' }}">
                        <i class="fas fa-cube"></i>
                        {{ __('portal.nav.prototypes') }}
                    </a>
                    <a href="{{ route('threed.manager.index') }}" class="nav-item {{ request()->routeIs('threed.manager.*') ? 'active' : '' }}">
                        <i class="fas fa-cubes"></i>
                        {{ __('portal.nav.threed') }}
                    </a>
                </div>

                <!-- Communication -->
                @php
                    // Cheap server-rendered seed (the JS poller refreshes it live on the
                    // chat page); cached briefly so every internal page doesn't re-scan.
                    $chatCounts = \Illuminate\Support\Facades\Cache::remember(
                        'chat_nav_counts:'.auth()->id(), 15,
                        fn () => app(\App\Services\ChatService::class)->unreadCounts(auth()->user())
                    );
                @endphp
                <div class="nav-section">
                    <div class="nav-section-title">{{ __('portal.nav.communication') }}</div>
                    <a href="{{ route('chat.index') }}" class="nav-item {{ request()->routeIs('chat.index','chat.show','chat.messages') ? 'active' : '' }}">
                        <i class="fas fa-comments"></i>
                        {{ __('portal.nav.team_chat') }}
                        <span id="navChatUnread" class="badge rounded-pill bg-danger ms-auto" {{ $chatCounts['total'] ? '' : 'hidden' }}>{{ $chatCounts['total'] > 9 ? '9+' : $chatCounts['total'] }}</span>
                    </a>
                    <a href="{{ route('chat.mentions') }}" class="nav-item {{ request()->routeIs('chat.mentions') ? 'active' : '' }}">
                        <i class="fas fa-at"></i>
                        {{ __('portal.nav.my_mentions') }}
                        <span id="navMentionsUnread" class="badge rounded-pill bg-danger ms-auto" {{ $chatCounts['mentions'] ? '' : 'hidden' }}>{{ $chatCounts['mentions'] > 9 ? '9+' : $chatCounts['mentions'] }}</span>
                    </a>
                    <a href="{{ route('notifications.preferences') }}" class="nav-item {{ request()->routeIs('notifications.preferences*') ? 'active' : '' }}">
                        <i class="fas fa-envelope-open-text"></i>
                        {{ __('portal.nav.notification_settings') }}
                    </a>
                </div>

                <!-- Projects -->
                <div class="nav-section">
                    <div class="nav-section-title">{{ __('portal.nav.projects') }}</div>
                    <a href="{{ route('projects.manager.index') }}" class="nav-item">
                        <i class="fas fa-folder"></i>
                        {{ __('portal.nav.all_projects') }}
                    </a>
                    <a href="{{ route('projects.proposals.index') }}" class="nav-item {{ request()->routeIs('projects.proposals.*','projects.propose.*') ? 'active' : '' }}">
                        <i class="fas fa-lightbulb"></i>
                        {{ __('portal.nav.proposals') }}
                    </a>
                    @if(auth()->user()->isManager())
                    <a href="{{ route('projects.create') }}" class="nav-item">
                        <i class="fas fa-plus"></i>
                        {{ __('portal.nav.new_project') }}
                    </a>
                    <a href="{{ route('projects.scope-changes.index') }}" class="nav-item">
                        <i class="fas fa-edit"></i>
                        {{ __('portal.nav.scope_changes') }}
                    </a>
                    @endif
                </div>

                <!-- Quote System -->
                <div class="nav-section">
                    <div class="nav-section-title">{{ __('portal.nav.quote_system') }}</div>
                    <a href="{{ route('pricing.quoting-tasks') }}" class="nav-item">
                        <i class="fas fa-file-invoice-dollar"></i>
                        {{ __('portal.nav.quoting_tasks') }}
                    </a>
                    <a href="{{ route('pricing.tool') }}" class="nav-item">
                        <i class="fas fa-calculator"></i>
                        {{ __('portal.nav.pricing_tool') }}
                    </a>
                    <a href="{{ route('scope-planner.index') }}" class="nav-item {{ request()->routeIs('scope-planner.*') ? 'active' : '' }}">
                        <i class="fas fa-wand-magic-sparkles"></i>
                        {{ __('portal.nav.scope_planner') }}
                    </a>
                    @if(auth()->user()->isManager())
                    <a href="{{ route('scope-prompts.edit') }}" class="nav-item {{ request()->routeIs('scope-prompts.*') ? 'active' : '' }}">
                        <i class="fas fa-robot"></i>
                        {{ __('portal.nav.ai_prompts') }}
                    </a>
                    @endif
                    @if(auth()->user()->isManager() || auth()->user()->isProjectManager())
                    <a href="{{ route('invoices.index') }}" class="nav-item {{ request()->routeIs('invoices.index') || request()->routeIs('invoices.create') || request()->routeIs('invoices.show') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice-dollar"></i>
                        {{ __('portal.invoices.manage_title') }}
                    </a>
                    @endif
                    @if(auth()->user()->isProjectManager())
                    <a href="{{ route('engagement.admin.grants.index') }}" class="nav-item {{ request()->routeIs('engagement.admin.grants.*') ? 'active' : '' }}">
                        <i class="fas fa-hand-holding-dollar"></i>
                        {{ __('engagement.admin.grants') }}
                    </a>
                    @endif
                    @if(auth()->user()->isManager())
                    <a href="{{ route('pricing.admin') }}" class="nav-item">
                        <i class="fas fa-cogs"></i>
                        {{ __('portal.nav.pricing_admin') }}
                    </a>
                    @endif
                </div>

                <!-- Meetings -->
                <div class="nav-section">
                    <div class="nav-section-title">{{ __('portal.nav.meetings') }}</div>
                    <a href="{{ route('time-slots.my-slots') }}" class="nav-item">
                        <i class="fas fa-calendar"></i>
                        {{ __('portal.nav.my_slots') }}
                    </a>
                    <a href="{{ route('meetings.internal.my-meetings') }}" class="nav-item">
                        <i class="fas fa-video"></i>
                        {{ __('portal.nav.my_meetings') }}
                    </a>
                    @if(auth()->user()->isManager())
                    <a href="{{ route('time-slots.team-slots') }}" class="nav-item">
                        <i class="fas fa-users-cog"></i>
                        {{ __('portal.nav.team_slots') }}
                    </a>
                    @endif
                </div>

                <!-- Admin (Manager Only) -->
                @if(auth()->user()->isManager())
                <div class="nav-section">
                    <div class="nav-section-title">{{ __('portal.nav.admin') }}</div>
                    <a href="{{ route('team.index') }}" class="nav-item">
                        <i class="fas fa-users"></i>
                        {{ __('portal.nav.team') }}
                    </a>
                    <a href="{{ route('permissions.index') }}" class="nav-item">
                        <i class="fas fa-shield-alt"></i>
                        {{ __('portal.nav.permissions') }}
                    </a>
                    <a href="{{ route('engagement.settings.edit') }}" class="nav-item {{ request()->routeIs('engagement.settings.*') ? 'active' : '' }}">
                        <i class="fas fa-sliders"></i>
                        {{ __('portal.nav.engagement_settings') }}
                    </a>
                    <a href="{{ route('engagement.admin.dashboard') }}" class="nav-item {{ request()->routeIs('engagement.admin.*') ? 'active' : '' }}">
                        <i class="fas fa-award"></i>
                        {{ __('engagement.admin.title') }}
                    </a>
                    <a href="{{ route('targets.admin.index') }}" class="nav-item {{ request()->routeIs('targets.admin.*') ? 'active' : '' }}">
                        <i class="fas fa-bullseye"></i>
                        {{ __('targets.admin.title') }}
                    </a>
                    <a href="{{ route('capacity.admin.dashboard') }}" class="nav-item {{ request()->routeIs('capacity.admin.*') ? 'active' : '' }}">
                        <i class="fas fa-gauge-high"></i>
                        {{ __('targets.cap.admin_title') }}
                    </a>
                    <a href="{{ route('improvement-ideas.manager.index') }}" class="nav-item {{ request()->routeIs('improvement-ideas.manager.*') ? 'active' : '' }}">
                        <i class="fas fa-clipboard-check"></i>
                        {{ __('portal.nav.improvement_reviews') }}
                    </a>
                    <a href="{{ route('inventory.index') }}" class="nav-item {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                        <i class="fas fa-boxes-stacked"></i>
                        {{ __('portal.nav.inventory') }}
                    </a>
                    <a href="{{ route('permissions.portal-clients') }}" class="nav-item">
                        <i class="fas fa-user-friends"></i>
                        {{ __('portal.permissions.portal_clients_nav') }}
                    </a>
                    <a href="{{ route('reports.financial') }}" class="nav-item">
                        <i class="fas fa-chart-line"></i>
                        {{ __('portal.internal.financial_reports') }}
                    </a>
                </div>
                @endif

                <!-- User Menu -->
                <div class="nav-section mt-auto">
                    <a href="{{ route('profile.show') }}" class="nav-item">
                        <i class="fas fa-user"></i>
                        {{ __('portal.nav.my_profile') }}
                    </a>
                    <a href="{{ route('profile.security') }}" class="nav-item">
                        <i class="fas fa-shield-alt"></i>
                        {{ __('portal.nav.security') }}
                    </a>
                    <a href="{{ route('logout') }}" class="nav-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i>
                        {{ __('portal.nav.logout') }}
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <header class="content-header">
                <div class="header-left">
                    <h1>@yield('title', 'Dashboard')</h1>
                </div>
                <div class="header-right d-flex align-items-center gap-2">
                    @include('partials.xp-bar')
                    @include('partials.target-chip')
                    @include('partials.notifications')
                    @include('partials.theme-toggle')
                    @include('partials.locale-switcher')
                    <div class="user-menu">
                        <span class="user-name">{{ auth()->user()->name }}</span>
                        {{-- <span class="user-role">{{ ucfirst(auth()->user()->role->value) }}</span> --}}
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </header>

            <!-- Content Body -->
            <div class="content-body">
                @if(isset($breadcrumbs) || View::hasSection('breadcrumbs'))
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('internal.dashboard') }}"><i class="fas fa-home"></i> {{ __('portal.nav.dashboard') }}</a></li>
                        @yield('breadcrumbs')
                    </ol>
                </nav>
                @endif
                
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
                
                @yield('content')
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('active');
        document.querySelector('.sidebar-overlay').classList.toggle('active');
    }

    window.showAppToast = function(message, type = 'info') {
        if (!message) {
            return;
        }

        let container = document.querySelector('.app-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container position-fixed top-0 end-0 p-3 app-toast-container';
            container.style.zIndex = '1080';
            document.body.appendChild(container);
        }

        const classMap = {
            success: 'text-bg-success',
            error: 'text-bg-danger',
            warning: 'text-bg-warning',
            info: 'text-bg-info',
        };

        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center border-0 mb-2 ${classMap[type] || classMap.info}`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close ${type === 'warning' ? '' : 'btn-close-white'} me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        container.appendChild(toastEl);

        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
            toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
            toast.show();
            return;
        }

        window.setTimeout(() => toastEl.remove(), 4000);
    };
    </script>
    <x-toast />
    @include('partials.flash-autodismiss')
    @stack('scripts')
</body>
</html>
