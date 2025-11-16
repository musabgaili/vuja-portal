<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Internal Dashboard') - VujaDe Platform</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="internal-dashboard">
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>VujaDe</h2>
                <small>Internal</small>
            </div>

            <!-- Sidebar Navigation -->
            <nav class="sidebar-nav">
                <!-- Dashboard -->
                <div class="nav-section">
                    <a href="{{ route('internal.dashboard') }}" class="nav-item">
                        <i class="fas fa-home"></i>
                        Dashboard
                    </a>
                </div>

                <!-- Service Requests -->
                <div class="nav-section">
                    <div class="nav-section-title">Services</div>
                    <a href="{{ route('ideas.manager.index') }}" class="nav-item">
                        <i class="fas fa-lightbulb"></i>
                        Ideas
                    </a>
                    <a href="{{ route('consultations.manager.index') }}" class="nav-item">
                        <i class="fas fa-comments"></i>
                        Consultations
                    </a>
                    <a href="{{ route('research.manager.index') }}" class="nav-item">
                        <i class="fas fa-search"></i>
                        Research
                    </a>
                    <a href="{{ route('ip.manager.index') }}" class="nav-item">
                        <i class="fas fa-file-contract"></i>
                        IP Reg
                    </a>
                    <a href="{{ route('copyright.manager.index') }}" class="nav-item">
                        <i class="fas fa-copyright"></i>
                        Copyright
                    </a>
                </div>

                <!-- Projects -->
                <div class="nav-section">
                    <div class="nav-section-title">Projects</div>
                    <a href="{{ route('projects.manager.index') }}" class="nav-item">
                        <i class="fas fa-folder"></i>
                        All Projects
                    </a>
                    @if(auth()->user()->isManager())
                    <a href="{{ route('projects.create') }}" class="nav-item">
                        <i class="fas fa-plus"></i>
                        New Project
                    </a>
                    <a href="{{ route('projects.scope-changes.index') }}" class="nav-item">
                        <i class="fas fa-edit"></i>
                        Scope Changes
                    </a>
                    @endif
                </div>

                <!-- Quote System -->
                <div class="nav-section">
                    <div class="nav-section-title">Quote System</div>
                    <a href="{{ route('pricing.quoting-tasks') }}" class="nav-item">
                        <i class="fas fa-file-invoice-dollar"></i>
                        Quoting Tasks
                    </a>
                    <a href="{{ route('pricing.tool') }}" class="nav-item">
                        <i class="fas fa-calculator"></i>
                        Pricing Tool
                    </a>
                    @if(auth()->user()->isManager())
                    <a href="{{ route('pricing.admin') }}" class="nav-item">
                        <i class="fas fa-cogs"></i>
                        Pricing Admin
                    </a>
                    @endif
                </div>

                <!-- Meetings -->
                <div class="nav-section">
                    <div class="nav-section-title">Meetings</div>
                    <a href="{{ route('time-slots.my-slots') }}" class="nav-item">
                        <i class="fas fa-calendar"></i>
                        My Slots
                    </a>
                    <a href="{{ route('meetings.internal.my-meetings') }}" class="nav-item">
                        <i class="fas fa-video"></i>
                        My Meetings
                    </a>
                    @if(auth()->user()->isManager())
                    <a href="{{ route('time-slots.team-slots') }}" class="nav-item">
                        <i class="fas fa-users-cog"></i>
                        Team Slots
                    </a>
                    @endif
                </div>

                <!-- Admin (Manager Only) -->
                @if(auth()->user()->isManager())
                <div class="nav-section">
                    <div class="nav-section-title">Admin</div>
                    <a href="{{ route('team.index') }}" class="nav-item">
                        <i class="fas fa-users"></i>
                        Team
                    </a>
                    <a href="{{ route('permissions.index') }}" class="nav-item">
                        <i class="fas fa-shield-alt"></i>
                        Permissions
                    </a>
                </div>
                @endif

                <!-- User Menu -->
                <div class="nav-section mt-auto">
                    <a href="{{ route('profile.show') }}" class="nav-item">
                        <i class="fas fa-user"></i>
                        My Profile
                    </a>
                    <a href="{{ route('profile.security') }}" class="nav-item">
                        <i class="fas fa-shield-alt"></i>
                        Security
                    </a>
                    <a href="{{ route('logout') }}" class="nav-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
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
                <div class="header-right">
                    <div class="user-menu">
                        <span class="user-name">{{ auth()->user()->name }}</span>
                        {{-- <span class="user-role">{{ ucfirst(auth()->user()->role->value) }}</span> --}}
                        <i class="fas fa-user"></i>
                    </a>
                </div>
            </header>

            <!-- Content Body -->
            <div class="content-body">
                @if(isset($breadcrumbs) || View::hasSection('breadcrumbs'))
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('internal.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
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
    </script>
    @stack('scripts')
</body>
</html>
