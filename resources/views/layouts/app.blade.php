<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dasbor') — ManufakTrack</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
    <!-- Tailwind CSS & ApexCharts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'bg-primary': 'var(--color-bg-primary)',
                        'bg-secondary': 'var(--color-bg-secondary)',
                        'bg-tertiary': 'var(--color-bg-tertiary)',
                        'text-primary': 'var(--color-text-primary)',
                        'text-secondary': 'var(--color-text-secondary)',
                        'text-muted': 'var(--color-text-muted)',
                        'border-default': 'var(--color-border)',
                        'border-strong': 'var(--color-border-strong)',
                        'accent': 'var(--color-accent)',
                        'accent-hover': 'var(--color-accent-hover)',
                        'accent-soft': 'var(--color-accent-soft)',
                        'highlight': 'var(--color-highlight-bg)',
                        
                        'badge-success-bg': 'var(--badge-success-bg)',
                        'badge-success-text': 'var(--badge-success-text)',
                        'badge-warning-bg': 'var(--badge-warning-bg)',
                        'badge-warning-text': 'var(--badge-warning-text)',
                        'badge-danger-bg': 'var(--badge-danger-bg)',
                        'badge-danger-text': 'var(--badge-danger-text)',
                        
                        critical: '#DC2626',
                        major: '#CA8A04',
                        closed: '#16A34A'
                    },
                    borderRadius: {
                        'card': '12px',
                        'pill': '999px',
                    },
                    boxShadow: {
                        'card': '0 1px 2px rgba(0,0,0,0.05), 0 1px 3px rgba(0,0,0,0.04)',
                    },
                    fontFamily: {
                        mono: ['Inter', 'sans-serif'],
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Montserrat', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* ============================================================
         * DESIGN SYSTEM — Brand palette Hariff Defense blue
         * ============================================================ */
        :root {
            /* Backgrounds */
            --color-bg-primary:    #F6FAFD;
            --color-bg-secondary:  #FFFFFF;
            --color-bg-tertiary:   #EAF2F9;
            /* Text */
            --color-text-primary:   #0A1931;
            --color-text-secondary: #1A3D63;
            --color-text-muted:     #4A7FA7;
            /* Borders */
            --color-border:         #D5E3F0;
            --color-border-strong:  #B3CFE5;
            /* Accent */
            --color-accent:         #1A3D63;
            --color-accent-hover:   #0A1931;
            --color-accent-soft:    #4A7FA7;
            /* Highlight / badge */
            --color-highlight-bg:   #B3CFE5;
            --color-highlight-text: #0A1931;
            /* Shadow */
            --color-card-shadow:    rgba(10, 25, 49, 0.08);
            /* Semantic badges */
            --badge-success-bg:   #DCFCE7;
            --badge-success-text: #166534;
            --badge-warning-bg:   #FEF3C7;
            --badge-warning-text: #92400E;
            --badge-danger-bg:    #FEE2E2;
            --badge-danger-text:  #991B1B;
            /* Input */
            --bg-input:     #FFFFFF;
            --border-input: #D5E3F0;
            --text-input:   #0A1931;
        }

        /* ============================================================
         * DARK MODE — uses body.dark (not .dark on html)
         * ============================================================ */
        body.dark {
            --color-bg-primary:    #0A1931;
            --color-bg-secondary:  #11233F;
            --color-bg-tertiary:   #1A3D63;
            --color-text-primary:   #F6FAFD;
            --color-text-secondary: #B3CFE5;
            --color-text-muted:     #7FA3C4;
            --color-border:         #1E3A5C;
            --color-border-strong:  #2C4E78;
            --color-accent:         #4A7FA7;
            --color-accent-hover:   #6B9CC3;
            --color-accent-soft:    #B3CFE5;
            --color-highlight-bg:   #1A3D63;
            --color-highlight-text: #F6FAFD;
            --color-card-shadow:    rgba(0,0,0,0.35);
            --badge-success-bg:   rgba(74, 222, 128, 0.15);
            --badge-success-text: #4ADE80;
            --badge-warning-bg:   rgba(251, 191, 36, 0.15);
            --badge-warning-text: #FBBF24;
            --badge-danger-bg:    rgba(248, 113, 113, 0.15);
            --badge-danger-text:  #F87171;
            --bg-input:     #11233F;
            --border-input: #1E3A5C;
            --text-input:   #F6FAFD;
        }

        /* Smooth transitions */
        *, *::before, *::after {
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }

        /* Typography */
        body {
            font-family: 'Inter', sans-serif !important;
            background-color: var(--color-bg-primary) !important;
            color: var(--color-text-primary) !important;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Inter', sans-serif !important;
            color: var(--color-text-primary) !important;
        }

        /* ============================================================
         * HARDCODED TAILWIND OVERRIDES — Light mode baseline
         * Make sure token classes map correctly
         * ============================================================ */
        .text-slate-900, .text-slate-800, .text-slate-700 {
            color: var(--color-text-primary) !important;
        }
        .text-slate-600, .text-slate-500 {
            color: var(--color-text-secondary) !important;
        }
        .text-slate-400 {
            color: var(--color-text-muted) !important;
        }
        .text-slate-300 {
            color: var(--color-text-muted) !important;
        }
        .bg-white {
            background-color: var(--color-bg-secondary) !important;
        }
        .bg-slate-50, .bg-slate-100 {
            background-color: var(--color-bg-tertiary) !important;
        }
        .divide-slate-50 > :not([hidden]) ~ :not([hidden]),
        .divide-slate-100 > :not([hidden]) ~ :not([hidden]) {
            border-color: var(--color-border) !important;
        }
        .border-slate-200, .border-slate-300 {
            border-color: var(--color-border) !important;
        }

        /* ============================================================
         * DARK MODE — comprehensive element overrides
         * ============================================================ */
        body.dark {
            background-color: var(--color-bg-primary) !important;
            color: var(--color-text-primary) !important;
        }

        /* Text overrides — prevent dark-on-dark */
        body.dark .text-slate-900,
        body.dark .text-slate-800,
        body.dark .text-slate-700,
        body.dark .text-slate-600,
        body.dark .text-gray-900,
        body.dark .text-gray-800,
        body.dark .text-gray-700 {
            color: var(--color-text-primary) !important;
        }
        body.dark .text-slate-500,
        body.dark .text-slate-400,
        body.dark .text-gray-500,
        body.dark .text-gray-400 {
            color: var(--color-text-secondary) !important;
        }
        body.dark .text-slate-300 {
            color: var(--color-text-muted) !important;
        }

        /* Background overrides */
        body.dark .bg-white {
            background-color: var(--color-bg-secondary) !important;
        }
        body.dark .bg-slate-50,
        body.dark .bg-slate-100 {
            background-color: var(--color-bg-tertiary) !important;
        }
        body.dark .bg-slate-200 {
            background-color: var(--color-border) !important;
        }

        /* Divider / border overrides */
        body.dark .divide-y > :not([hidden]) ~ :not([hidden]),
        body.dark .divide-slate-50 > :not([hidden]) ~ :not([hidden]),
        body.dark .divide-slate-100 > :not([hidden]) ~ :not([hidden]) {
            border-color: var(--color-border) !important;
        }
        body.dark .border-slate-200,
        body.dark .border-slate-300,
        body.dark .border-slate-100,
        body.dark .border-t,
        body.dark .border-b {
            border-color: var(--color-border) !important;
        }

        /* Inputs in dark mode */
        body.dark input,
        body.dark select,
        body.dark textarea {
            background-color: var(--bg-input) !important;
            border-color: var(--border-input) !important;
            color: var(--text-input) !important;
        }
        body.dark input::placeholder,
        body.dark textarea::placeholder {
            color: var(--color-text-muted) !important;
        }
        /* <select> options need explicit color */
        body.dark select option {
            background-color: #11233F;
            color: #F6FAFD;
        }
        input, select, textarea {
            background-color: var(--bg-input) !important;
            border-color: var(--border-input) !important;
            color: var(--text-input) !important;
        }
        input::placeholder, select::placeholder, textarea::placeholder {
            color: var(--color-text-muted) !important;
        }
        input:focus, select:focus, textarea:focus {
            border-color: var(--color-accent) !important;
            box-shadow: 0 0 0 3px rgba(74, 127, 167, 0.2) !important;
            outline: none !important;
        }

        /* Semantic badge overrides in dark mode */
        body.dark .bg-red-50 { background-color: var(--badge-danger-bg) !important; }
        body.dark .text-red-700, body.dark .text-red-600 { color: var(--badge-danger-text) !important; }
        body.dark .border-red-200 { border-color: rgba(248,113,113,0.3) !important; }

        body.dark .bg-green-50 { background-color: var(--badge-success-bg) !important; }
        body.dark .text-green-700, body.dark .text-green-600 { color: var(--badge-success-text) !important; }
        body.dark .border-green-200 { border-color: rgba(74,222,128,0.3) !important; }

        body.dark .bg-yellow-50 { background-color: var(--badge-warning-bg) !important; }
        body.dark .text-yellow-700, body.dark .text-yellow-600 { color: var(--badge-warning-text) !important; }
        body.dark .border-yellow-200 { border-color: rgba(251,191,36,0.3) !important; }

        body.dark .bg-amber-600 { background-color: var(--badge-warning-bg) !important; }
        body.dark .text-amber-600 { color: var(--badge-warning-text) !important; }
        body.dark .text-amber-500 { color: #FBBF24 !important; }

        /* Hover states */
        body.dark .hover\:bg-bg-tertiary:hover,
        body.dark .hover\:bg-slate-50:hover,
        body.dark .hover\:bg-slate-100:hover {
            background-color: var(--color-bg-tertiary) !important;
        }

        /* ============================================================
         * SIDEBAR — always navy, both modes
         * ============================================================ */
        aside {
            background-color: #0A1931 !important;
            border-right-color: #071120 !important;
        }
        body.dark aside {
            background-color: #071120 !important;
            border-right-color: #071120 !important;
        }
        /* All sidebar text forced white */
        aside * {
            color: inherit;
        }
        /* Sidebar nav links */
        aside nav a {
            color: #B3CFE5 !important;
            transition: all 0.2s ease;
        }
        aside nav a:hover {
            background-color: rgba(255,255,255,0.08) !important;
            color: #FFFFFF !important;
        }
        /* Active nav link */
        aside nav a.active-nav,
        aside nav a[data-active="true"] {
            background-color: #1A3D63 !important;
            color: #FFFFFF !important;
            border-left: 3px solid #4A7FA7 !important;
        }
        body.dark aside nav a:hover {
            background-color: rgba(255,255,255,0.08) !important;
            color: #FFFFFF !important;
        }
        /* Sidebar text/icon always visible */
        aside .text-white { color: #FFFFFF !important; }
        aside .text-slate-200, aside .text-slate-300 { color: #B3CFE5 !important; }
        /* Dark mode sidebar depth */
        body.dark aside {
            box-shadow: 2px 0 12px rgba(0,0,0,0.4) !important;
        }

        /* ============================================================
         * CARD & LAYOUT
         * ============================================================ */
        .premium-card {
            background-color: var(--color-bg-secondary) !important;
            border: 1px solid var(--color-border) !important;
            border-radius: 12px !important;
            box-shadow: 0 1px 3px var(--color-card-shadow) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        .premium-card:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 24px var(--color-card-shadow) !important;
        }

        /* ============================================================
         * SIDEBAR COLLAPSE (2-MODE: EXPANDED vs ICON-ONLY)
         * ============================================================ */
        .sidebar-transition {
            transition: width 0.2s cubic-bezier(0.4, 0, 0.2, 1), padding 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        @media (min-width: 768px) {
            body.sidebar-collapsed aside {
                width: 4.5rem !important; /* ~72px sempit icon-only */
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }
            body.sidebar-collapsed aside .sidebar-brand-text,
            body.sidebar-collapsed aside .sidebar-label,
            body.sidebar-collapsed aside .user-info-text,
            body.sidebar-collapsed aside .theme-toggle-text,
            body.sidebar-collapsed aside .logout-text {
                display: none !important;
            }
            body.sidebar-collapsed aside .sidebar-header-row {
                flex-direction: column !important;
                justify-content: center !important;
                align-items: center !important;
                gap: 1rem !important;
            }
            body.sidebar-collapsed aside .nav-item {
                justify-content: center !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
            body.sidebar-collapsed aside .user-footer-row {
                flex-direction: column !important;
                justify-content: center !important;
                align-items: center !important;
            }
            body.sidebar-collapsed aside .user-actions-row {
                flex-direction: column !important;
                width: 100% !important;
                gap: 0.5rem !important;
            }
            body.sidebar-collapsed aside .logout-btn {
                padding-left: 0 !important;
                padding-right: 0 !important;
                justify-content: center !important;
            }
            body.sidebar-collapsed aside #theme-toggle {
                width: 100% !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
        }
        /* Tooltip muncul saat collapsed + hover */
        .sidebar-tooltip {
            display: none;
        }
        @media (min-width: 768px) {
            body.sidebar-collapsed .nav-item:hover .sidebar-tooltip {
                display: block;
            }
        }
        #floating-controls { display: none !important; }

        @media (max-width: 767.98px) {
            body.sidebar-mobile-open aside { transform: translateX(0) !important; }
            body.sidebar-mobile-open #sidebar-overlay { opacity: 1 !important; visibility: visible !important; }
        }

        /* ============================================================
         * LOGOUT & THEME BUTTON
         * ============================================================ */
        .logout-btn {
            color: #FFFFFF !important;
        }

        /* ============================================================
         * SCROLLBAR
         * ============================================================ */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: var(--color-bg-tertiary); }
        ::-webkit-scrollbar-thumb { background: var(--color-border-strong); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--color-text-muted); }

        /* ============================================================
         * PAGE LOADING SYSTEM
         * ============================================================ */

        /* ---- Top progress bar (slim, YouTube-style) ---- */
        #mfg-progress-bar {
            position: fixed;
            top: 0; left: 0;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, #1A3D63, #4A7FA7, #6BADD6);
            z-index: 99999;
            transition: width 0.3s ease, opacity 0.4s ease;
            opacity: 0;
            border-radius: 0 2px 2px 0;
            box-shadow: 0 0 10px rgba(74, 127, 167, 0.6);
        }
        #mfg-progress-bar.active {
            opacity: 1;
        }
        #mfg-progress-bar::after {
            content: '';
            display: block;
            position: absolute;
            right: 0; top: 0;
            width: 80px;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5));
            animation: mfg-shimmer 0.8s ease-in-out infinite;
        }
        @keyframes mfg-shimmer {
            0%   { opacity: 0; }
            50%  { opacity: 1; }
            100% { opacity: 0; }
        }

        /* ---- Full-page overlay (semi-transparent, for page change) ---- */
        #mfg-page-overlay {
            position: fixed;
            inset: 0;
            background: rgba(10, 25, 49, 0);
            z-index: 99998;
            pointer-events: none;
            transition: background 0.25s ease;
        }
        #mfg-page-overlay.active {
            pointer-events: all;
            background: rgba(10, 25, 49, 0.12);
        }
        body.dark #mfg-page-overlay.active {
            background: rgba(0, 0, 0, 0.25);
        }

        /* ---- Filter spinner overlay (for forms with data tables) ---- */
        #mfg-filter-overlay {
            position: fixed;
            inset: 0;
            z-index: 99997;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        #mfg-filter-overlay.active {
            pointer-events: all;
            opacity: 1;
        }
        #mfg-filter-overlay .filter-spinner-card {
            background: var(--color-bg-secondary);
            border: 1px solid var(--color-border-default);
            border-radius: 16px;
            padding: 24px 32px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 20px 60px rgba(10, 25, 49, 0.15), 0 4px 16px rgba(10, 25, 49, 0.08);
        }
        .mfg-spin-ring {
            width: 28px;
            height: 28px;
            border: 3px solid var(--color-bg-tertiary);
            border-top-color: #1A3D63;
            border-radius: 50%;
            animation: mfg-spin 0.7s linear infinite;
            flex-shrink: 0;
        }
        body.dark .mfg-spin-ring {
            border-color: var(--color-bg-tertiary);
            border-top-color: #4A7FA7;
        }
        @keyframes mfg-spin {
            to { transform: rotate(360deg); }
        }
        .mfg-filter-text {
            font-size: 13px;
            font-weight: 600;
            color: var(--color-text-primary);
            white-space: nowrap;
        }
        .mfg-filter-sub {
            font-size: 11px;
            color: var(--color-text-muted);
            margin-top: 2px;
        }
    </style>
    @yield('styles')
</head>
<body class="bg-bg-primary text-text-primary font-sans min-h-screen flex flex-col md:flex-row">

    <!-- ============================================================
         GLOBAL LOADING SYSTEM — Progress bar + overlays
         ============================================================ -->
    <div id="mfg-progress-bar"></div>
    <div id="mfg-page-overlay"></div>
    <div id="mfg-filter-overlay">
        <div class="filter-spinner-card">
            <div class="mfg-spin-ring"></div>
            <div>
                <div class="mfg-filter-text" id="mfg-filter-label">Memuat data…</div>
                <div class="mfg-filter-sub">Mohon tunggu sebentar</div>
            </div>
        </div>
    </div>
    <script>
        (function() {
            const theme = localStorage.getItem('theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (theme === 'dark' || (!theme && systemPrefersDark)) {
                document.body.classList.add('dark');
            }
        })();
    </script>

    <!-- Mobile Sticky Top Header -->
    <header class="md:hidden flex items-center justify-between px-4 py-3 bg-white dark:bg-card border-b border-panel-border z-30 sticky top-0 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 relative flex items-center justify-center">
                <img src="{{ asset('LOGO LOGO LAGI.png') }}" alt="Logo Hariff Defense" class="w-full h-full object-contain brand-logo-light">
                <img src="{{ asset('LOGO LOGO LAGI.png') }}" alt="Logo Hariff Defense" class="w-full h-full object-contain brand-logo-dark hidden">
            </div>
            <div>
                <span class="font-extrabold text-xs text-text-primary block leading-tight tracking-wide">ManufakTrack</span>
                <span class="block text-[7px] text-text-secondary font-medium tracking-wider leading-none mt-0.5">By Hariff Defense</span>
            </div>
        </div>
        <button id="mobile-menu-toggle" class="p-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-500 hover:text-blue-600 dark:bg-gray-800 dark:border-gray-700 transition-all flex items-center justify-center cursor-pointer shadow-sm">
            <i class="bi bi-list text-xl"></i>
        </button>
    </header>

    <!-- Mobile Sidebar Dark Overlay Backdrop -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-slate-900/50 z-40 hidden transition-opacity duration-300"></div>

    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-[#1A3D63] border-r border-[#1A3D63] flex flex-col justify-between p-6 shrink-0 sidebar-transition max-md:fixed max-md:inset-y-0 max-md:left-0 max-md:z-50 max-md:-translate-x-full max-md:shadow-2xl">
        <div>
            <!-- Brand Logo & Toggle -->
            <div class="sidebar-header-row flex items-center justify-between gap-3 mb-8">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-11 h-11 shrink-0 relative flex items-center justify-center">
                        <img src="{{ asset('LOGO LOGO LAGI.png') }}" alt="Logo Hariff Defense" class="w-full h-full object-contain brand-logo-light">
                        <img src="{{ asset('LOGO LOGO LAGI.png') }}" alt="Logo Hariff Defense" class="w-full h-full object-contain brand-logo-dark hidden">
                    </div>
                    <div class="sidebar-brand-text min-w-0">
                        <span class="font-extrabold text-sm text-white block leading-tight tracking-wide">ManufakTrack</span>
                        <span class="block text-[9px] text-slate-200 font-medium tracking-wider mt-0.5">By Hariff Defense</span>
                    </div>
                </div>
                <button id="sidebar-toggle-in" class="p-1.5 rounded-lg bg-white/10 border border-white/20 text-white hover:text-white hover:bg-white/20 transition-all flex items-center justify-center cursor-pointer shadow-sm shrink-0 w-8 h-8" title="Sembunyikan Sidebar">
                    <i id="sidebar-toggle-icon" class="bi bi-chevron-left text-sm"></i>
                </button>
            </div>

            <!-- Nav Links -->
            <nav class="space-y-1.5">
                @auth
                    <a href="{{ route('dashboard') }}" class="nav-item group relative flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('dashboard') ? 'bg-[#0A1931] text-white border-l-2 border-[#4A7FA7] font-bold' : 'text-white hover:bg-[#0A1931] hover:text-white' }}">
                        <i class="bi bi-bar-chart-line text-base shrink-0"></i>
                        <span class="sidebar-label whitespace-nowrap">Laporan Umum</span>
                        <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1.5 rounded-md bg-[#0A1931] text-white text-xs font-semibold whitespace-nowrap z-50 shadow-lg border border-[#1E3A5C] pointer-events-none">Laporan Umum</span>
                    </a>
                    <a href="{{ route('laporan-khusus.index') }}" class="nav-item group relative flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('laporan-khusus.*') ? 'bg-[#0A1931] text-white border-l-2 border-[#4A7FA7] font-bold' : 'text-white hover:bg-[#0A1931] hover:text-white' }}">
                        <i class="bi bi-file-earmark-text text-base shrink-0"></i>
                        <span class="sidebar-label whitespace-nowrap">Laporan Khusus</span>
                        <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1.5 rounded-md bg-[#0A1931] text-white text-xs font-semibold whitespace-nowrap z-50 shadow-lg border border-[#1E3A5C] pointer-events-none">Laporan Khusus</span>
                    </a>

                    {{-- Divider --}}
                    <div class="my-2 border-t border-[#0A1931]"></div>

                    <a href="{{ route('import.upload') }}" class="nav-item group relative flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('import.upload') || request()->routeIs('import.progress') ? 'bg-[#0A1931] text-white border-l-2 border-[#4A7FA7] font-bold' : 'text-white hover:bg-[#0A1931] hover:text-white' }}">
                        <i class="bi bi-cloud-upload text-base shrink-0"></i>
                        <span class="sidebar-label whitespace-nowrap">Import Data .sql</span>
                        <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1.5 rounded-md bg-[#0A1931] text-white text-xs font-semibold whitespace-nowrap z-50 shadow-lg border border-[#1E3A5C] pointer-events-none">Import Data .sql</span>
                    </a>
                    <a href="{{ route('import.history') }}" class="nav-item group relative flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('import.history') ? 'bg-[#0A1931] text-white border-l-2 border-[#4A7FA7] font-bold' : 'text-white hover:bg-[#0A1931] hover:text-white' }}">
                        <i class="bi bi-clock-history text-base shrink-0"></i>
                        <span class="sidebar-label whitespace-nowrap">Riwayat Import</span>
                        <span class="sidebar-tooltip absolute left-full ml-3 px-2.5 py-1.5 rounded-md bg-[#0A1931] text-white text-xs font-semibold whitespace-nowrap z-50 shadow-lg border border-[#1E3A5C] pointer-events-none">Riwayat Import</span>
                    </a>
                @endauth
            </nav>
        </div>

        <!-- User profile footer -->
        @auth
            @php
                $authUser = auth()->user();
                $profilePhotoUrl = $authUser->profile_photo_url;
                $profileInitials = strtoupper(substr($authUser->name ?? '', 0, 2));
            @endphp
            <div class="user-footer-row border-t border-[#0A1931] pt-4 mt-8 flex flex-col justify-between">
                <div class="flex items-center gap-3">
                    @if($profilePhotoUrl)
                        <img src="{{ $profilePhotoUrl }}" alt="Foto profil" class="h-9 w-9 rounded-full object-cover border border-white/20 shrink-0" title="{{ $authUser->name }}">
                    @else
                        <div class="h-9 w-9 rounded-full bg-white/10 flex items-center justify-center font-bold text-sm text-white shrink-0" title="{{ $authUser->name }}">
                            {{ $profileInitials }}
                        </div>
                    @endif
                    <div class="user-info-text overflow-hidden min-w-0">
                        <div class="text-sm font-semibold text-white truncate">{{ $authUser->name }}</div>
                        <div class="text-xs text-slate-300 font-mono tracking-wider uppercase">{{ $authUser->role }}</div>
                    </div>
                </div>
                <div class="user-actions-row flex gap-2 mt-4">
                    <button id="theme-toggle" type="button" class="px-3 py-2 border border-white/20 hover:bg-white/10 text-white rounded-lg text-xs font-semibold transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-sm shrink-0" style="flex: 1;" title="Ubah Tema">
                        <i id="theme-toggle-icon" class="bi bi-moon-stars shrink-0"></i>
                        <span class="theme-toggle-text">Tema</span>
                    </button>
                    <form action="{{ route('logout') }}" method="POST" class="flex-1" style="flex: 2;">
                        @csrf
                        <button type="submit" class="logout-btn w-full flex items-center justify-center gap-2 px-3 py-2 border border-white/20 hover:bg-white/10 text-white rounded-lg text-xs font-semibold transition-all shadow-sm shrink-0" title="Keluar">
                            <i class="bi bi-box-arrow-right shrink-0"></i> <span class="logout-text">Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        @endauth
    </aside>

    <!-- Main Workspace -->
    <main class="flex-1 p-4 md:p-8 overflow-y-auto relative">
        <div class="content-wrapper">
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
        </div>
    </main>

    @yield('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleIn = document.getElementById('sidebar-toggle-in');
            const themeToggle = document.getElementById('theme-toggle');
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const sidebarOverlay = document.getElementById('sidebar-overlay');
            const body = document.body;
            
            // Selalu mulai EXPANDED sesuai spesifikasi (tidak memuat dari localStorage)
            body.classList.remove('sidebar-collapsed');
            
            function toggleSidebar() {
                if (window.innerWidth < 768) {
                    body.classList.remove('sidebar-mobile-open');
                } else {
                    const collapsed = body.classList.toggle('sidebar-collapsed');
                    const toggleIcon = document.getElementById('sidebar-toggle-icon');
                    if (toggleIcon) {
                        toggleIcon.className = collapsed ? 'bi bi-chevron-right text-sm' : 'bi bi-chevron-left text-sm';
                    }
                    if (toggleIn) {
                        toggleIn.title = collapsed ? 'Tampilkan Sidebar' : 'Sembunyikan Sidebar';
                    }
                }
            }

            if (toggleIn) {
                toggleIn.addEventListener('click', toggleSidebar);
            }

            // Mobile specific drawer listeners
            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', function() {
                    body.classList.add('sidebar-mobile-open');
                });
            }
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    body.classList.remove('sidebar-mobile-open');
                });
            }

            // Theme toggle UI updater
            function updateThemeUI(isDark) {
                const iconClass = isDark ? 'bi-sun' : 'bi-moon-stars';
                const titleText = isDark ? 'Ubah ke Mode Terang' : 'Ubah ke Mode Gelap';
                const themeText = isDark ? 'Terang' : 'Gelap';
                
                if (themeToggle) {
                    themeToggle.innerHTML = `<i id="theme-toggle-icon" class="bi ${iconClass} shrink-0"></i><span class="theme-toggle-text">${themeText}</span>`;
                    themeToggle.title = titleText;
                }
            }

            // Read the class list directly (pre-applied by top immediate script)
            const initialDark = body.classList.contains('dark');
            updateThemeUI(initialDark);

            function toggleTheme() {
                const isDark = !body.classList.contains('dark');
                body.classList.toggle('dark', isDark);
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                updateThemeUI(isDark);
                
                // Broadcast custom event for other widgets (e.g. ApexCharts)
                document.dispatchEvent(new CustomEvent('theme-changed', {
                    detail: { theme: isDark ? 'dark' : 'light' }
                }));
            }

            if (themeToggle) {
                themeToggle.addEventListener('click', toggleTheme);
            }
        });
    </script>
    @stack('scripts')

    <!-- ============================================================
         GLOBAL PAGE LOADING SCRIPT
         ============================================================ -->
    <script>
    (function() {
        'use strict';

        const bar     = document.getElementById('mfg-progress-bar');
        const overlay = document.getElementById('mfg-page-overlay');
        const fltOver = document.getElementById('mfg-filter-overlay');
        const fltLabel = document.getElementById('mfg-filter-label');

        let barTimer = null;
        let barPct   = 0;

        // ---- Progress bar helpers ----
        function barStart() {
            if (!bar) return;
            clearTimeout(barTimer);
            barPct = 10;
            bar.style.width    = barPct + '%';
            bar.style.opacity  = '1';
            bar.classList.add('active');
            // Slowly creep to 85%
            (function creep() {
                if (barPct >= 85) return;
                barPct += Math.random() * 8;
                if (barPct > 85) barPct = 85;
                bar.style.width = barPct + '%';
                barTimer = setTimeout(creep, 250 + Math.random() * 200);
            })();
        }

        function barFinish() {
            if (!bar) return;
            clearTimeout(barTimer);
            bar.style.width   = '100%';
            bar.style.opacity = '0';
            bar.classList.remove('active');
            setTimeout(() => { bar.style.width = '0'; }, 450);
        }

        // ---- Show filter spinner ----
        function showFilterSpinner(label) {
            if (!fltOver) return;
            if (fltLabel) fltLabel.textContent = label || 'Memuat data…';
            fltOver.classList.add('active');
            overlay.classList.add('active');
            barStart();
        }

        // ---- Page nav loading (for sidebar links & normal <a> nav) ----
        function showPageNav() {
            barStart();
            overlay.classList.add('active');
        }

        // Finish when the page actually loads
        window.addEventListener('pageshow', function(e) {
            barFinish();
            overlay.classList.remove('active');
            if (fltOver) fltOver.classList.remove('active');
        });

        // ---- Hook sidebar navigation links ----
        document.querySelectorAll('aside nav a[href]').forEach(function(link) {
            link.addEventListener('click', function(e) {
                const href = link.getAttribute('href');
                // Skip anchors and external links
                if (!href || href.startsWith('#') || href.startsWith('javascript') || href.startsWith('mailto')) return;
                showPageNav();
            });
        });

        // ---- Hook filter FORMs (GET forms with selects/inputs) ----
        // Dashboard Laporan Umum filter form
        var dashForm = document.getElementById('dashboard-filter-form');
        if (dashForm) {
            dashForm.addEventListener('submit', function() {
                showFilterSpinner('Menerapkan filter…');
            });
        }

        // Laporan Khusus product dropdown (auto-submit)
        var prodSelect = document.querySelector('select#product_id');
        if (prodSelect) {
            // Override native onchange so we control timing
            prodSelect.removeAttribute('onchange');
            prodSelect.addEventListener('change', function() {
                showFilterSpinner('Menganalisis data produk…');
                setTimeout(function() { prodSelect.form.submit(); }, 80);
            });
        }

        // ---- Hook Export Excel buttons ----
        document.querySelectorAll('a[href*="export"]').forEach(function(el) {
            el.addEventListener('click', function() {
                barStart();
                // Don't show full overlay for downloads — just bar
                setTimeout(barFinish, 3000);
            });
        });

        // ---- Immediately finish bar if page already loaded ----
        if (document.readyState === 'complete') {
            barFinish();
        }

    })();
    </script>
</body>
</html>
