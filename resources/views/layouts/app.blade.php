<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - BugTrack MFG</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS & ApexCharts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        obsidian: '#060814',
                        'panel-bg': '#111625',
                        'panel-border': '#1F293D',
                        'neon-cyan': '#00F0FF',
                        'neon-pink': '#FF2E93',
                        'neon-green': '#00FF85',
                        'neon-amber': '#F59E0B',
                        accent: '#3B82F6',
                        slate: {
                            950: '#0B0F19',
                        },
                        critical: '#EF4444',
                        major: '#F59E0B',
                        closed: '#10B981',
                        spam: '#8B5CF6'
                    },
                    fontFamily: {
                        mono: ['JetBrains Mono', 'Fira Code', 'Courier New', 'monospace'],
                        sans: ['Inter', 'Outfit', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* Force font override globally to prevent CDN/Tailwind fallback conflicts */
        body {
            font-family: 'Inter', 'Outfit', sans-serif !important;
        }
        h1, h2, h3, h4, h5, h6, .brand, .font-heading {
            font-family: 'Outfit', sans-serif !important;
        }
        .font-mono, code, kbd, samp, pre {
            font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', monospace !important;
        }

        /* Global Cyberpunk Color Theme Overrides */
        .bg-obsidian, body {
            background-color: #060814 !important;
        }
        .bg-slate-950, .bg-slate-950\/80, .bg-slate-950\/60, .bg-slate-950\/40, .bg-slate-950\/70 {
            background-color: #060814 !important;
        }
        .bg-slate-900, .bg-slate-900\/40, .bg-slate-900\/60, .bg-slate-[#0A0E1A] {
            background-color: #111625 !important;
        }
        .border-slate-800, .border-slate-850, .border-slate-800\/50, .border-slate-850\/60, .border-slate-800\/60, .border-panel-border {
            border-color: #1F293D !important;
        }
        .divide-slate-800\/50 > :not([hidden]) ~ :not([hidden]),
        .divide-slate-800\/60 > :not([hidden]) ~ :not([hidden]),
        .divide-panel-border\/60 > :not([hidden]) ~ :not([hidden]) {
            border-color: #1F293D !important;
        }
        input, select, textarea {
            background-color: #060814 !important;
            border-color: #1F293D !important;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #00F0FF !important; /* Focus glows Neon Cyan */
            box-shadow: 0 0 10px rgba(0, 240, 255, 0.2) !important;
        }

        /* Pulse animations for critical issues */
        @keyframes alert-pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.05); }
        }
        .critical-pulse {
            animation: alert-pulse 2s infinite ease-in-out;
        }
        /* Custom scrollbar for data dense tables */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0B0F19;
        }
        ::-webkit-scrollbar-thumb {
            background: #1E293B;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #334155;
        }
    </style>
    @yield('styles')
</head>
<body class="bg-obsidian text-slate-100 font-sans min-h-screen flex">

    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-[#0A0E1A] border-r border-panel-border flex flex-col justify-between p-6 shrink-0">
        <div>
            <!-- Brand Logo -->
            <div class="flex items-center gap-3 mb-8">
                <div class="h-9 w-9 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                    <i class="bi bi-cpu text-white text-xl"></i>
                </div>
                <div>
                    <span class="font-extrabold text-lg bg-clip-text text-transparent bg-gradient-to-r from-slate-100 to-slate-400">BugTrack MFG</span>
                    <span class="block text-[10px] text-slate-500 font-mono tracking-widest uppercase">PT HARIFF SYSTEM</span>
                </div>
            </div>

            <!-- Nav Links -->
            <nav class="space-y-1.5">
                @auth
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('dashboard') ? 'bg-indigo-600/15 text-white border-l-2 border-indigo-500' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                            <i class="bi bi-speedometer2 text-base"></i> Dashboard Analitik
                        </a>
                        <a href="{{ route('master.projects.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('master.projects.*') ? 'bg-indigo-600/15 text-white border-l-2 border-indigo-500' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                            <i class="bi bi-kanban text-base"></i> Kelola Project
                        </a>
                        <a href="{{ route('master.serial_numbers.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('master.serial_numbers.*') ? 'bg-indigo-600/15 text-white border-l-2 border-indigo-500' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                            <i class="bi bi-hash text-base"></i> Kelola Serial Number
                        </a>
                    @endif

                    <a href="{{ route('bugs.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('bugs.index') && !request()->has('status') ? 'bg-indigo-600/15 text-white border-l-2 border-indigo-500' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                        <i class="bi bi-list-task text-base"></i> 
                        <span>{{ auth()->user()->role === 'reporter' ? 'Riwayat Laporanku' : 'Queue Kerja Bug' }}</span>
                    </a>

                    @if(auth()->user()->role === 'reporter' || auth()->user()->role === 'admin')
                        <a href="{{ route('bugs.create') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('bugs.create') ? 'bg-indigo-600/15 text-white border-l-2 border-indigo-500' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                            <i class="bi bi-plus-circle text-base"></i> Laporkan Bug Baru
                        </a>
                    @endif
                @endauth
            </nav>
        </div>

        <!-- User profile footer -->
        @auth
            <div class="border-t border-slate-800 pt-4 mt-8">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-full bg-slate-800 flex items-center justify-center font-bold text-sm text-slate-300">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-sm font-semibold text-slate-200 truncate">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-slate-500 font-mono tracking-wider uppercase">{{ auth()->user()->role }}</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 border border-slate-800 hover:bg-slate-800 text-slate-400 hover:text-slate-200 rounded-lg text-xs font-semibold transition-all">
                        <i class="bi bi-box-arrow-right"></i> Keluar Sistem
                    </button>
                </form>
            </div>
        @endauth
    </aside>

    <!-- Main Workspace -->
    <main class="flex-1 p-8 overflow-y-auto">
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center gap-3 text-sm">
                <i class="bi bi-check-circle-fill text-base"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 flex items-center gap-3 text-sm">
                <i class="bi bi-exclamation-triangle-fill text-base"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    @yield('scripts')
</body>
</html>
