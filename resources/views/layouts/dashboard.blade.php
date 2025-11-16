<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - VujaDe Platform</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="client-dashboard">
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
                <div class="user-info">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                    <div class="user-details">
                        <h3>{{ auth()->user()->name }}</h3>
                        <p>Client</p>
                    </div>
                </div>
            </div>

            <!-- Sidebar Navigation -->
            <nav class="sidebar-nav">
                <!-- Dashboard -->
                <div class="nav-section">
                    <a href="{{ route('dashboard') }}" class="nav-item">
                        <i class="fas fa-home"></i>
                        Dashboard
                    </a>
                </div>

                <!-- Projects & Requests -->
                <div class="nav-section">
                    <div class="nav-section-title">Work</div>
                    <a href="{{ route('projects.client.index') }}" class="nav-item">
                        <i class="fas fa-folder"></i>
                        My Projects
                    </a>
                    <a href="{{ route('client.requests') }}" class="nav-item">
                        <i class="fas fa-list"></i>
                        My Requests
                    </a>
                    <a href="{{ route('services.index') }}" class="nav-item">
                        <i class="fas fa-plus"></i>
                        New Request
                    </a>
                </div>

                <!-- Meetings -->
                <div class="nav-section">
                    <div class="nav-section-title">Meetings</div>
                    <a href="{{ route('meetings.available-slots') }}" class="nav-item">
                        <i class="fas fa-calendar-plus"></i>
                        Book Meeting
                    </a>
                    <a href="{{ route('meetings.my-meetings') }}" class="nav-item">
                        <i class="fas fa-video"></i>
                        My Meetings
                    </a>
                </div>

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
    </script>
    @stack('scripts')
</body>
</html>
