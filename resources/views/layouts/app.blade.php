<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - BugTrack MFG</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <!-- Feather icons or direct SVG icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @yield('styles')
</head>
<body>
    <div class="app-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="brand">
                <i class="bi bi-cpu-fill"></i>
                <span>BugTrack MFG</span>
            </div>
            
            <nav class="nav-links">
                @auth
                    @if(auth()->user()->role === 'admin')
                        <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <a href="{{ route('dashboard') }}">
                                <i class="bi bi-speedometer2"></i> Dashboard Analitik
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('master.projects.*') ? 'active' : '' }}">
                            <a href="{{ route('master.projects.index') }}">
                                <i class="bi bi-kanban"></i> Kelola Project
                            </a>
                        </li>
                        <li class="nav-item {{ request()->routeIs('master.serial_numbers.*') ? 'active' : '' }}">
                            <a href="{{ route('master.serial_numbers.index') }}">
                                <i class="bi bi-hash"></i> Kelola Serial Number
                            </a>
                        </li>
                    @endif

                    <!-- Common Bug Queue List for mechanics, reporters and admin -->
                    <li class="nav-item {{ request()->routeIs('bugs.index') && !request()->has('status') ? 'active' : '' }}">
                        <a href="{{ route('bugs.index') }}">
                            <i class="bi bi-list-task"></i> 
                            {{ auth()->user()->role === 'reporter' ? 'Riwayat Laporanku' : 'Queue Kerja Bug' }}
                        </a>
                    </li>

                    @if(auth()->user()->role === 'reporter' || auth()->user()->role === 'admin')
                        <li class="nav-item {{ request()->routeIs('bugs.create') ? 'active' : '' }}">
                            <a href="{{ route('bugs.create') }}">
                                <i class="bi bi-plus-circle"></i> Laporkan Bug Baru
                            </a>
                        </li>
                    @endif
                @endauth
            </nav>

            @auth
                <div class="user-profile">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ auth()->user()->role }}</div>
                    <form action="{{ route('logout') }}" method="POST" style="margin-top: 0.75rem;">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%; justify-content: center;">
                            <i class="bi bi-box-arrow-right"></i> Keluar
                        </button>
                    </form>
                </div>
            @endauth
        </aside>

        <!-- Main Workspace -->
        <main class="main-content">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @yield('scripts')
</body>
</html>
