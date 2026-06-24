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
                        obsidian: '#FFFFFF',
                        'panel-bg': '#FFFFFF',
                        'panel-border': '#E2E8F0',
                        accent: '#2563EB',
                        slate: {
                            950: '#F8FAFC',
                        },
                        critical: '#DC2626',
                        major: '#CA8A04',
                        closed: '#16A34A',
                        spam: '#475569'
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
            background-color: #F8FAFC !important;
            color: #1E293B !important;
        }
        h1, h2, h3, h4, h5, h6, .brand, .font-heading {
            font-family: 'Outfit', sans-serif !important;
            color: #1E293B !important;
        }
        .font-mono, code, kbd, samp, pre {
            font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', monospace !important;
        }

        /* Force light mode text overrides to prevent illegible styles from dark-theme classes */
        .text-slate-100, .text-slate-200, .text-slate-250, .text-slate-300, .text-slate-350 {
            color: #1E293B !important;
        }
        .text-slate-400, .text-slate-450, .text-slate-500, .text-slate-550 {
            color: #64748B !important;
        }
        .text-slate-600, .text-slate-650 {
            color: #94A3B8 !important;
        }

        /* Global Light Mode Color Theme Overrides */
        .bg-obsidian {
            background-color: #FFFFFF !important;
        }
        .bg-slate-950, .bg-slate-950\/80, .bg-slate-950\/60, .bg-slate-950\/40, .bg-slate-950\/70 {
            background-color: #F8FAFC !important;
        }
        .bg-slate-900, .bg-slate-900\/40, .bg-slate-900\/60, .bg-slate-[#0A0E1A] {
            background-color: #FFFFFF !important;
        }
        .border-slate-800, .border-slate-850, .border-slate-800\/50, .border-slate-850\/60, .border-slate-800\/60, .border-panel-border {
            border-color: #E2E8F0 !important;
        }
        .divide-slate-800\/50 > :not([hidden]) ~ :not([hidden]),
        .divide-slate-800\/60 > :not([hidden]) ~ :not([hidden]),
        .divide-panel-border\/60 > :not([hidden]) ~ :not([hidden]) {
            border-color: #E2E8F0 !important;
        }
        input, select, textarea {
            background-color: #FFFFFF !important;
            border-color: #E2E8F0 !important;
            color: #1E293B !important;
        }
        input::placeholder, select::placeholder, textarea::placeholder {
            color: #94A3B8 !important;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #2563EB !important; /* Focus corporate blue */
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
        }

        /* Custom scrollbar for data dense tables */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #F1F5F9;
        }
        ::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94A3B8;
        }
    </style>
    @yield('styles')
</head>
<body class="bg-[#F8FAFC] text-slate-800 font-sans min-h-screen flex">

    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-white border-r border-panel-border flex flex-col justify-between p-6 shrink-0">
        <div>
            <!-- Brand Logo -->
            <div class="flex items-center gap-3 mb-8">
                <div class="h-9 w-9 rounded-lg bg-blue-600 flex items-center justify-center shadow-md">
                    <i class="bi bi-cpu text-white text-xl"></i>
                </div>
                <div>
                    <span class="font-extrabold text-lg text-slate-800">BugTrack MFG</span>
                    <span class="block text-[10px] text-slate-500 font-mono tracking-widest uppercase">PT HARIFF SYSTEM</span>
                </div>
            </div>

            <!-- Nav Links -->
            <nav class="space-y-1.5">
                @auth
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('dashboard') ? 'text-blue-600 border-l-2 border-blue-600 bg-blue-50/30 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50' }}">
                            <i class="bi bi-speedometer2 text-base"></i> Dashboard Analitik
                        </a>
                        <a href="{{ route('master.projects.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('master.projects.*') ? 'text-blue-600 border-l-2 border-blue-600 bg-blue-50/30 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50' }}">
                            <i class="bi bi-kanban text-base"></i> Kelola Project
                        </a>
                        <a href="{{ route('master.serial_numbers.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('master.serial_numbers.*') ? 'text-blue-600 border-l-2 border-blue-600 bg-blue-50/30 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50' }}">
                            <i class="bi bi-hash text-base"></i> Kelola Serial Number
                        </a>
                        <a href="{{ route('master.devices.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('master.devices.*') ? 'text-blue-600 border-l-2 border-blue-600 bg-blue-50/30 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50' }}">
                            <i class="bi bi-cpu text-base"></i> Kelola Device
                        </a>
                    @endif

                    <a href="{{ route('bugs.index') }}" class="flex items-center justify-between w-full px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('bugs.index') && !request()->has('status') ? 'text-blue-600 border-l-2 border-blue-600 bg-blue-50/30 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50' }}">
                        <div class="flex items-center gap-3">
                            <i class="bi bi-list-task text-base"></i> 
                            <span>{{ auth()->user()->role === 'reporter' ? 'Riwayat Laporanku' : 'Queue Kerja Bug' }}</span>
                        </div>
                        @php
                            $unreadFeedbackCount = \App\Models\BugFeedback::where('to_user_id', auth()->id())->where('is_read', false)->count();
                        @endphp
                        @if($unreadFeedbackCount > 0)
                            <span class="inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold leading-none text-white bg-[#DC2626] rounded-full shadow-md font-mono animate-pulse">{{ $unreadFeedbackCount }}</span>
                        @endif
                    </a>

                    @if(auth()->user()->role === 'reporter' || auth()->user()->role === 'admin')
                        <a href="{{ route('bugs.create') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('bugs.create') ? 'text-blue-600 border-l-2 border-blue-600 bg-blue-50/30 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50' }}">
                            <i class="bi bi-plus-circle text-base"></i> Laporkan Bug Baru
                        </a>
                    @endif
                @endauth
            </nav>
        </div>

        <!-- User profile footer -->
        @auth
            <div class="border-t border-slate-200 pt-4 mt-8">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-full bg-slate-100 flex items-center justify-center font-bold text-sm text-slate-700">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-sm font-semibold text-slate-700 truncate">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-slate-400 font-mono tracking-wider uppercase">{{ auth()->user()->role }}</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 border border-slate-200 hover:bg-slate-50 text-slate-500 rounded-lg text-xs font-semibold transition-all">
                        <i class="bi bi-box-arrow-right"></i> Keluar Sistem
                    </button>
                </form>
            </div>
        @endauth
    </aside>

    <!-- Main Workspace -->
    <main class="flex-1 p-8 overflow-y-auto">
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-250 text-green-700 flex items-center gap-3 text-sm">
                <i class="bi bi-check-circle-fill text-base"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-250 text-red-700 flex items-center gap-3 text-sm">
                <i class="bi bi-exclamation-triangle-fill text-base"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    @yield('scripts')
</body>
</html>
