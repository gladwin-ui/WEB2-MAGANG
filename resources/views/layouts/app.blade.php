<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dasbor') - Sistem Pelacakan Manufaktur PT Hariff</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@700;800;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
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
                        // Official design tokens
                        'bg-main': 'var(--bg-main)',
                        'bg-sidebar': 'var(--bg-sidebar)',
                        'bg-card': 'var(--bg-card)',
                        'bg-card-hover': 'var(--bg-card-hover)',
                        'border-default': 'var(--border-default)',
                        'text-primary': 'var(--text-primary)',
                        'text-secondary': 'var(--text-secondary)',
                        'text-tertiary': 'var(--text-tertiary)',

                        'accent-primary': '#2563EB',
                        'accent-primary-hover': '#1D4ED8',
                        'accent-primary-soft': 'var(--accent-primary-soft)',

                        'badge-success-bg': 'var(--badge-success-bg)',
                        'badge-success-text': 'var(--badge-success-text)',
                        'badge-warning-bg': 'var(--badge-warning-bg)',
                        'badge-warning-text': 'var(--badge-warning-text)',
                        'badge-danger-bg': 'var(--badge-danger-bg)',
                        'badge-danger-text': 'var(--badge-danger-text)',
                        'badge-neutral-bg': 'var(--badge-neutral-bg)',
                        'badge-neutral-text': 'var(--badge-neutral-text)',

                        // Fallback/Legacy properties
                        obsidian: 'var(--bg-card)',
                        'panel-bg': 'var(--bg-card)',
                        'panel-border': 'var(--border-default)',
                        accent: '#2563EB',
                        slate: {
                            950: 'var(--bg-main)',
                        },
                        critical: '#DC2626',
                        major: '#CA8A04',
                        closed: '#16A34A',
                        spam: '#475569'
                    },
                    borderRadius: {
                        'card': '12px',
                        'pill': '999px',
                    },
                    boxShadow: {
                        'card': '0 1px 2px rgba(0,0,0,0.05), 0 1px 3px rgba(0,0,0,0.04)',
                    },
                    fontFamily: {
                        mono: ['JetBrains Mono', 'Fira Code', 'Courier New', 'monospace'],
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
        :root {
            --bg-main: #FFFFFF;
            --bg-sidebar: #FFFFFF;
            --bg-card: #FFFFFF;
            --bg-card-hover: #F8FAFC;
            
            --border-default: #E2E8F0;
            --text-primary: #1E293B;
            --text-secondary: #64748B;
            --text-tertiary: #94A3B8;
            
            --bg-input: #FFFFFF;
            --border-input: #E2E8F0;
            --text-input: #1E293B;

            --bg-hover-item: #F8FAFC;
            
            --accent-primary-soft: #EFF6FF;
            --badge-success-bg: #DCFCE7;
            --badge-success-text: #16A34A;
            --badge-warning-bg: #FEF9C3;
            --badge-warning-text: #CA8A04;
            --badge-danger-bg: #FEE2E2;
            --badge-danger-text: #DC2626;
            --badge-neutral-bg: #F1F5F9;
            --badge-neutral-text: #475569;
        }

        body.dark {
            --bg-main: #0B0F19;
            --bg-sidebar: #111827;
            --bg-card: #1F2937;
            --bg-card-hover: #374151;
            
            --border-default: #374151;
            --text-primary: #F9FAFB;
            --text-secondary: #9CA3AF;
            --text-tertiary: #6B7280;
            
            --bg-input: #111827;
            --border-input: #374151;
            --text-input: #F9FAFB;

            --bg-hover-item: #374151;
            
            --accent-primary-soft: #1E3A8A;
            --badge-success-bg: rgba(22, 163, 74, 0.2);
            --badge-success-text: #4ADE80;
            --badge-warning-bg: rgba(202, 138, 4, 0.2);
            --badge-warning-text: #FBBF24;
            --badge-danger-bg: rgba(220, 38, 38, 0.2);
            --badge-danger-text: #F87171;
            --badge-neutral-bg: rgba(71, 85, 105, 0.2);
            --badge-neutral-text: #94A3B8;
        }

        /* Smooth transitions for theme switching */
        body, aside, main, div, button, a, input, select, textarea, span, h1, h2, h3, h4, h5, h6 {
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }

        /* Force font override globally to prevent CDN/Tailwind fallback conflicts */
        body {
            font-family: 'Inter', sans-serif !important;
            background-color: var(--bg-main) !important;
            color: var(--text-primary) !important;
        }
        h1, h2, h3, h4, h5, h6, .brand, .font-heading {
            font-family: 'Montserrat', sans-serif !important;
            font-weight: 800 !important;
            letter-spacing: 0.01em;
            color: var(--text-primary) !important;
        }
        .font-mono, code, kbd, samp, pre {
            font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', monospace !important;
        }

        /* Force text colors in dark mode to prevent illegible styles from tailwind default slate colors */
        .text-slate-100, .text-slate-200, .text-slate-250, .text-slate-300, .text-slate-350 {
            color: var(--text-primary) !important;
        }
        .text-slate-400, .text-slate-450, .text-slate-500, .text-slate-550 {
            color: var(--text-secondary) !important;
        }
        .text-slate-600, .text-slate-650 {
            color: var(--text-tertiary) !important;
        }

        /* Global Light/Dark Theme Class overrides */
        .bg-obsidian {
            background-color: var(--bg-card) !important;
        }
        .bg-slate-950, .bg-slate-950\/80, .bg-slate-950\/60, .bg-slate-950\/40, .bg-slate-950\/70 {
            background-color: var(--bg-main) !important;
        }
        .bg-slate-900, .bg-slate-900\/40, .bg-slate-900\/60, .bg-slate-[#0A0E1A] {
            background-color: var(--bg-card) !important;
        }
        .border-slate-800, .border-slate-850, .border-slate-800\/50, .border-slate-850\/60, .border-slate-800\/60, .border-panel-border {
            border-color: var(--border-default) !important;
        }
        .divide-slate-800\/50 > :not([hidden]) ~ :not([hidden]),
        .divide-slate-800\/60 > :not([hidden]) ~ :not([hidden]),
        .divide-panel-border\/60 > :not([hidden]) ~ :not([hidden]) {
            border-color: var(--border-default) !important;
        }
        input, select, textarea {
            background-color: var(--bg-input) !important;
            border-color: var(--border-input) !important;
            color: var(--text-input) !important;
        }
        input::placeholder, select::placeholder, textarea::placeholder {
            color: var(--text-tertiary) !important;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #2563EB !important; /* Focus corporate blue */
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
        }

        /* Global overrides for standard Light Mode classes when Dark Mode is active */
        body.dark {
            background-color: var(--bg-main) !important;
            color: var(--text-primary) !important;
        }
        body.dark aside {
            background-color: var(--bg-sidebar) !important;
            border-color: var(--border-default) !important;
        }
        body.dark .bg-white {
            background-color: var(--bg-card) !important;
        }
        body.dark .bg-slate-50 {
            background-color: var(--bg-main) !important;
        }
        body.dark .bg-slate-100 {
            background-color: var(--bg-card-hover) !important;
        }
        body.dark .bg-slate-200 {
            background-color: var(--border-default) !important;
        }
        body.dark .bg-blue-50\/30 {
            background-color: transparent !important;
            border-left-color: #60a5fa !important;
            color: #60a5fa !important;
        }
        body.dark .border-slate-200,
        body.dark .border-slate-300,
        body.dark .border-panel-border {
            border-color: var(--border-default) !important;
        }
        body.dark .text-slate-800,
        body.dark .text-slate-700,
        body.dark .text-slate-900 {
            color: var(--text-primary) !important;
        }
        body.dark .text-slate-500,
        body.dark .text-slate-600 {
            color: var(--text-secondary) !important;
        }
        body.dark .text-slate-400 {
            color: var(--text-tertiary) !important;
        }

        /* Hover states in dark mode */
        body.dark .hover\:bg-slate-50:hover {
            background-color: var(--bg-card-hover) !important;
        }
        body.dark .hover\:bg-slate-100:hover {
            background-color: var(--bg-card-hover) !important;
        }
        body.dark .hover\:bg-slate-100\/50:hover {
            background-color: var(--bg-card-hover) !important;
            color: #60a5fa !important;
        }
        body.dark .hover\:text-slate-800:hover {
            color: var(--text-primary) !important;
        }
        body.dark .hover\:text-blue-600:hover {
            color: #60a5fa !important;
        }
        body.dark .text-blue-600 {
            color: #60a5fa !important;
        }
        body.dark .hover\:text-blue-700:hover {
            color: #93c5fd !important;
        }
        body.dark .border-t {
            border-color: var(--border-default) !important;
        }
        body.dark .divide-y > :not([hidden]) ~ :not([hidden]) {
            border-color: var(--border-default) !important;
        }
        body.dark .bg-slate-50\/50 {
            background-color: rgba(15, 23, 42, 0.5) !important;
        }
        
        /* Active menu list items: transparent background, blue text (Light Mode override) */
        .bg-blue-50\/30 {
            background-color: transparent !important;
            border-left-width: 2px !important;
            border-left-style: solid !important;
            border-left-color: #2563EB !important;
            color: #2563EB !important;
        }

        /* Hover states for menu items in Light Mode */
        .hover\:bg-slate-100\/50:hover {
            background-color: var(--bg-card-hover) !important;
            color: #2563EB !important;
        }

        /* Sidebar links active & hover overrides to ensure no yellow background */
        aside nav a {
            transition: all 0.2s ease;
        }
        /* Active states */
        aside nav a.bg-blue-50\/30,
        aside nav a[class*="bg-blue-50"] {
            background-color: transparent !important;
            border-left: 2px solid #2563EB !important;
            color: #2563EB !important;
            font-weight: 700 !important;
        }
        /* Hover states */
        aside nav a:hover {
            background-color: var(--bg-card-hover) !important;
            color: #2563EB !important;
        }

        /* Dark mode overrides for active/hover states */
        body.dark aside nav a.bg-blue-50\/30,
        body.dark aside nav a[class*="bg-blue-50"] {
            background-color: transparent !important;
            border-left-color: #60a5fa !important;
            color: #60a5fa !important;
        }
        body.dark aside nav a:hover {
            background-color: var(--bg-card-hover) !important;
            color: #60a5fa !important;
        }

        /* Brand logo switches for dark mode */
        body.dark .brand-logo-light {
            display: none !important;
        }
        body.dark .brand-logo-dark {
            display: block !important;
        }
        
        /* Premium Card overrides */
        body.dark .premium-card {
            background-color: var(--bg-card) !important;
            border-color: var(--border-default) !important;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.2), 0 2px 4px -1px rgba(0,0,0,0.1) !important;
        }
        body.dark .premium-card:hover {
            border-color: #4B5563 !important;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.3), 0 4px 6px -2px rgba(0,0,0,0.3) !important;
        }

        /* Status and badge overrides */
        body.dark .bg-red-50 {
            background-color: var(--badge-danger-bg) !important;
        }
        body.dark .text-red-700 {
            color: var(--badge-danger-text) !important;
        }
        body.dark .border-red-200, body.dark .border-red-250 {
            border-color: rgba(220, 38, 38, 0.3) !important;
        }
        body.dark .bg-green-50 {
            background-color: var(--badge-success-bg) !important;
        }
        body.dark .text-green-700 {
            color: var(--badge-success-text) !important;
        }
        body.dark .border-green-200, body.dark .border-green-250 {
            border-color: rgba(22, 163, 74, 0.3) !important;
        }
        body.dark .bg-yellow-50 {
            background-color: var(--badge-warning-bg) !important;
        }
        body.dark .text-yellow-700 {
            color: var(--badge-warning-text) !important;
        }
        body.dark .border-yellow-200, body.dark .border-yellow-250 {
            border-color: rgba(202, 138, 4, 0.3) !important;
        }
        body.dark .bg-indigo-50 {
            background-color: rgba(99, 102, 241, 0.2) !important;
        }
        body.dark .text-indigo-650 {
            color: #a5b4fc !important;
        }

        /* Collapsible Sidebar Styles */
        .sidebar-transition {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), padding 0.3s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        .sidebar-collapsed aside {
            width: 0px !important;
            padding-left: 0px !important;
            padding-right: 0px !important;
            border-right-width: 0px !important;
            overflow: hidden !important;
        }

        /* Show floating controls only when collapsed */
        #floating-controls {
            display: none;
        }
        body.sidebar-collapsed #floating-controls {
            display: flex !important;
        }

        /* Shift main content's child to the right when collapsed to avoid overlapping with floating controls */
        .content-wrapper {
            transition: padding-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        body.sidebar-collapsed .content-wrapper {
            padding-left: 6.5rem !important; /* pl-26 to give space for absolute controls container */
        }

        /* Premium Styling Upgrades */
        .premium-card {
            background-color: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            border-radius: 12px !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            position: relative !important;
            overflow: hidden !important;
        }

        .premium-card:hover {
            transform: translateY(-4px) !important;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05), 0 4px 6px -2px rgba(0,0,0,0.05) !important;
            border-color: #CBD5E1 !important;
        }

        /* Premium accent line on card top */
        .premium-card-accent::before {
            content: '' !important;
            display: block !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 3px !important;
            background: #F59E0B !important;
            z-index: 10 !important;
        }

        /* Primary Button / Gradient Accent */
        .btn-premium-gradient {
            background: #F59E0B !important;
            color: #1E293B !important;
            transition: all 0.2s ease-in-out !important;
        }

        .btn-premium-gradient:hover {
            background: #D97706 !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3) !important;
        }

        .btn-premium-gradient:active {
            transform: translateY(0) !important;
            box-shadow: none !important;
        }

        /* Pastikan teks tombol keluar tetap terbaca di semua mode */
        .logout-btn {
            color: #1E293B !important;
        }
        body.dark .logout-btn {
            background-color: #334155 !important;
            border-color: #475569 !important;
            color: #F8FAFC !important;
        }
        body.dark .logout-btn:hover {
            background-color: #475569 !important;
            border-color: #64748B !important;
            color: #FFFFFF !important;
        }

        /* Solid Yellow style overrides for all submit, primary, and blue action buttons */
        button[type="submit"],
        .btn-primary,
        button.bg-blue-600,
        a.bg-blue-600,
        a.btn-premium-gradient {
            background-color: #F59E0B !important;
            background-image: none !important;
            color: #1E293B !important;
            border-color: #F59E0B !important;
        }
        button[type="submit"]:hover,
        .btn-primary:hover,
        button.bg-blue-600:hover,
        a.bg-blue-600:hover,
        a.btn-premium-gradient:hover,
        .hover\:bg-blue-700:hover {
            background-color: #D97706 !important;
            background-image: none !important;
            color: #1E293B !important;
            border-color: #D97706 !important;
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

        /* Scrollbar dark mode adjustments */
        body.dark ::-webkit-scrollbar-track {
            background: #1F2937;
        }
        body.dark ::-webkit-scrollbar-thumb {
            background: #4B5563;
        }
        body.dark ::-webkit-scrollbar-thumb:hover {
            background: #6B7280;
        }
    </style>
    @yield('styles')
</head>
<body class="bg-[#F8FAFC] text-slate-800 font-sans min-h-screen flex">
    <script>
        (function() {
            const theme = localStorage.getItem('theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (theme === 'dark' || (!theme && systemPrefersDark)) {
                document.body.classList.add('dark');
            }
        })();
    </script>

    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-white border-r border-panel-border flex flex-col justify-between p-6 shrink-0 sidebar-transition">
        <div>
            <!-- Brand Logo & Toggle -->
            <div class="flex items-center justify-between gap-3 mb-8">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-11 h-11 shrink-0 relative">
                        <img src="{{ asset('Logo.png') }}" alt="Logo PT Hariff" class="absolute inset-0 w-full h-full object-contain rounded brand-logo-light">
                        <img src="{{ asset('logo-darkmode.jpg') }}" alt="Logo PT Hariff" class="absolute inset-0 w-full h-full object-contain rounded brand-logo-dark hidden">
                    </div>
                    <div class="min-w-0">
                        <span class="font-extrabold text-xs text-slate-800 block leading-tight uppercase tracking-wider">Sistem Pelacakan</span>
                        <span class="font-extrabold text-xs text-slate-800 block leading-tight uppercase tracking-wider">Manufaktur</span>
                        <span class="block text-[8px] text-slate-400 font-mono tracking-widest uppercase mt-1">BY PT HARIFF</span>
                    </div>
                </div>
                <button id="sidebar-toggle-in" class="p-1.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-500 hover:text-blue-600 hover:bg-slate-100 transition-all flex items-center justify-center cursor-pointer shadow-sm shrink-0" title="Sembunyikan Sidebar">
                    <i class="bi bi-chevron-left text-sm"></i>
                </button>
            </div>

            <!-- Nav Links -->
            <nav class="space-y-1.5">
                @auth
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('dashboard') ? 'text-blue-600 border-l-2 border-blue-600 bg-blue-50/30 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50' }}">
                            <i class="bi bi-speedometer2 text-base"></i> Dashboard Analitik
                        </a>
                        <a href="{{ route('master.projects.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('master.projects.*') ? 'text-blue-600 border-l-2 border-blue-600 bg-blue-50/30 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50' }}">
                            <i class="bi bi-kanban text-base"></i> Kelola Proyek
                        </a>
                        <a href="{{ route('master.serial_numbers.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('master.serial_numbers.*') ? 'text-blue-600 border-l-2 border-blue-600 bg-blue-50/30 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50' }}">
                            <i class="bi bi-hash text-base"></i> Kelola Nomor Seri
                        </a>
                        <a href="{{ route('master.devices.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('master.devices.*') ? 'text-blue-600 border-l-2 border-blue-600 bg-blue-50/30 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50' }}">
                            <i class="bi bi-cpu text-base"></i> Kelola Perangkat
                        </a>
                    @endif

                    @if(auth()->user()->role === 'reporter')
                        <a href="{{ route('bugs.my') }}" class="flex items-center justify-between w-full px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('bugs.my') ? 'text-blue-600 border-l-2 border-blue-600 bg-blue-50/30 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50' }}">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-list-task text-base"></i> 
                                <span>Riwayat Laporan</span>
                            </div>
                        </a>
                        <a href="{{ route('bugs.chat.index') }}" class="flex items-center justify-between w-full px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('bugs.chat.index') ? 'text-blue-600 border-l-2 border-blue-600 bg-blue-50/30 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50' }}">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-chat-left-text text-base"></i> 
                                <span>Riwayat Obrolan</span>
                            </div>
                        </a>

                    @elseif(auth()->user()->role === 'mekanik')
                        <a href="{{ route('bugs.queue') }}" class="flex items-center justify-between w-full px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('bugs.queue') ? 'text-blue-600 border-l-2 border-blue-600 bg-blue-50/30 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50' }}">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-list-task text-base"></i> 
                                <span>Antrean Kerja Bug</span>
                            </div>
                        </a>
                        <a href="{{ route('bugs.chat.index') }}" class="flex items-center justify-between w-full px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('bugs.chat.index') ? 'text-blue-600 border-l-2 border-blue-600 bg-blue-50/30 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50' }}">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-chat-left-text text-base"></i> 
                                <span>Riwayat Obrolan</span>
                            </div>
                        </a>
                    @endif

                    @if(auth()->user()->role === 'reporter' || auth()->user()->role === 'admin')
                        <a href="{{ route('bugs.create') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('bugs.create') ? 'text-blue-600 border-l-2 border-blue-600 bg-blue-50/30 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50' }}">
                            <i class="bi bi-plus-circle text-base"></i> Laporkan Bug Baru
                        </a>
                        @if(auth()->user()->role === 'reporter')
                            <a href="{{ route('users.settings.edit') }}" class="flex items-center justify-between w-full px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('users.settings.*') ? 'text-blue-600 border-l-2 border-blue-600 bg-blue-50/30 font-bold' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100/50' }}">
                                <div class="flex items-center gap-3">
                                    <i class="bi bi-gear text-base"></i>
                                    <span>Pengaturan</span>
                                </div>
                            </a>
                        @endif
                    @endif
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
            <div class="border-t border-slate-200 pt-4 mt-8">
                <div class="flex items-center gap-3">
                    @if($profilePhotoUrl)
                        <img src="{{ $profilePhotoUrl }}" alt="Foto profil" class="h-9 w-9 rounded-full object-cover border border-slate-200">
                    @else
                        <div class="h-9 w-9 rounded-full bg-slate-100 flex items-center justify-center font-bold text-sm text-slate-700">
                            {{ $profileInitials }}
                        </div>
                    @endif
                    <div class="overflow-hidden">
                        <div class="text-sm font-semibold text-slate-700 truncate">{{ $authUser->name }}</div>
                        <div class="text-xs text-slate-400 font-mono tracking-wider uppercase">{{ $authUser->role }}</div>
                    </div>
                </div>
                <div class="flex gap-2 mt-3">
                    <button id="theme-toggle" type="button" class="px-3 py-2 border border-slate-200 hover:bg-slate-50 text-slate-500 rounded-lg text-xs font-semibold transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-sm" style="flex: 1;" title="Ubah Tema">
                        <i class="bi bi-moon-stars"></i>
                    </button>
                    <form action="{{ route('logout') }}" method="POST" class="flex-1" style="flex: 2;">
                        @csrf
                        <button type="submit" class="logout-btn w-full flex items-center justify-center gap-2 px-3 py-2 border border-slate-200 hover:bg-slate-50 text-slate-500 rounded-lg text-xs font-semibold transition-all shadow-sm">
                            <i class="bi bi-box-arrow-right"></i> Keluar
                        </button>
                    </form>
                </div>
            </div>
        @endauth
    </aside>

    <!-- Main Workspace -->
    <main class="flex-1 p-8 overflow-y-auto relative">
        <!-- Floating Controls Container (Visible only when sidebar is collapsed) -->
        <div id="floating-controls" class="hidden absolute top-8 left-8 flex items-center gap-2 z-50">
            <button id="sidebar-toggle-out" class="p-2 rounded-lg bg-white border border-slate-200 text-slate-600 hover:text-blue-600 hover:bg-slate-50 transition-all shadow-sm flex items-center justify-center cursor-pointer" title="Tampilkan Sidebar">
                <i class="bi bi-list text-lg"></i>
            </button>
            <button id="theme-toggle-out" class="p-2 rounded-lg bg-white border border-slate-200 text-slate-600 hover:text-blue-600 hover:bg-slate-50 transition-all shadow-sm flex items-center justify-center cursor-pointer" title="Ubah Tema">
                <i class="bi bi-moon-stars text-lg"></i>
            </button>
        </div>

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
            const toggleOut = document.getElementById('sidebar-toggle-out');
            const themeToggle = document.getElementById('theme-toggle');
            const themeToggleOut = document.getElementById('theme-toggle-out');
            const body = document.body;
            
            // Load sidebar state from localStorage
            const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
            if (isCollapsed) {
                body.classList.add('sidebar-collapsed');
            }
            
            function toggleSidebar() {
                body.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebar-collapsed', body.classList.contains('sidebar-collapsed'));
            }

            if (toggleIn) {
                toggleIn.addEventListener('click', toggleSidebar);
            }
            if (toggleOut) {
                toggleOut.addEventListener('click', toggleSidebar);
            }

            // Theme toggle UI updater
            function updateThemeUI(isDark) {
                const iconClass = isDark ? 'bi-sun' : 'bi-moon-stars';
                const titleText = isDark ? 'Ubah ke Mode Terang' : 'Ubah ke Mode Gelap';
                
                if (themeToggle) {
                    themeToggle.innerHTML = `<i class="bi ${iconClass} text-sm"></i>`;
                    themeToggle.title = titleText;
                }
                if (themeToggleOut) {
                    themeToggleOut.innerHTML = `<i class="bi ${iconClass} text-lg"></i>`;
                    themeToggleOut.title = titleText;
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
            if (themeToggleOut) {
                themeToggleOut.addEventListener('click', toggleTheme);
            }
        });
    </script>
</body>
</html>
