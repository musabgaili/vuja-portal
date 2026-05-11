<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('portal.dashboard')) - {{ __('portal.platform_name') }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="client-dashboard">
    <!-- Mobile Menu Toggle -->
    <button type="button" class="mobile-menu-toggle" onclick="toggleSidebar()" aria-label="{{ __('portal.app.toggle_nav') }}">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>VujaDe</h2>
                <div class="user-info">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                    <div class="user-details">
                        <h3>{{ auth()->user()->name }}</h3>
                        <p>{{ __('portal.layout_client.role_badge') }}</p>
                    </div>
                </div>
            </div>

            <!-- Sidebar Navigation -->
            <nav class="sidebar-nav">
                <!-- Dashboard -->
                <div class="nav-section">
                    <a href="{{ route('dashboard') }}" class="nav-item">
                        <i class="fas fa-home"></i>
                        {{ __('portal.layout_client.nav_dashboard') }}
                    </a>
                </div>

                <!-- Projects & Requests -->
                <div class="nav-section">
                    <div class="nav-section-title">{{ __('portal.layout_client.section_work') }}</div>
                    <a href="{{ route('projects.client.index') }}" class="nav-item">
                        <i class="fas fa-folder"></i>
                        {{ __('portal.layout_client.nav_my_projects') }}
                    </a>
                    <a href="{{ route('client.requests') }}" class="nav-item">
                        <i class="fas fa-list"></i>
                        {{ __('portal.layout_client.nav_my_requests') }}
                    </a>
                    <a href="{{ route('services.index') }}" class="nav-item">
                        <i class="fas fa-plus"></i>
                        {{ __('portal.layout_client.nav_new_request') }}
                    </a>
                </div>

                <!-- Meetings -->
                <div class="nav-section">
                    <div class="nav-section-title">{{ __('portal.layout_client.section_meetings') }}</div>
                    <a href="{{ route('meetings.available-slots') }}" class="nav-item">
                        <i class="fas fa-calendar-plus"></i>
                        {{ __('portal.layout_client.nav_book_meeting') }}
                    </a>
                    <a href="{{ route('meetings.my-meetings') }}" class="nav-item">
                        <i class="fas fa-video"></i>
                        {{ __('portal.layout_client.nav_my_meetings') }}
                    </a>
                </div>

                <!-- User Menu -->
                <div class="nav-section mt-auto">
                    <a href="{{ route('profile.show') }}" class="nav-item">
                        <i class="fas fa-user"></i>
                        {{ __('portal.layout_client.nav_my_profile') }}
                    </a>
                    <a href="{{ route('profile.security') }}" class="nav-item">
                        <i class="fas fa-shield-alt"></i>
                        {{ __('portal.layout_client.nav_security') }}
                    </a>
                    <a href="{{ route('logout') }}" class="nav-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i>
                        {{ __('portal.layout_client.nav_logout') }}
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
                    <h1>@yield('title', __('portal.dashboard'))</h1>
                </div>
                <div class="header-right d-flex align-items-center gap-2">
                    @include('partials.locale-switcher')
                    <div class="user-menu">
                        <span class="user-name">{{ auth()->user()->name }}</span>
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </header>

            <!-- Content Body -->
            <div class="content-body">
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
    @stack('scripts')
</body>
</html>
