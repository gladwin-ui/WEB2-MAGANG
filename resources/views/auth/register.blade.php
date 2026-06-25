<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Manufacturing Tracking System by PT Hariff</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@700;800;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        slate: {
                            950: '#F8FAFC',
                        }
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* Force font override globally */
        body {
            font-family: 'Inter', sans-serif !important;
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }
        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Montserrat', sans-serif !important;
            font-weight: 800 !important;
        }
        .font-mono, code {
            font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', monospace !important;
        }

        /* Global Color Theme Overrides */
        body {
            background-color: #FFFFFF !important;
            color: #1E293B !important;
        }
        .bg-slate-950 {
            background-color: #FFFFFF !important;
        }
        .bg-slate-900\/60 {
            background-color: #FFFFFF !important;
        }
        .border-slate-800, .border-slate-850 {
            border-color: #E2E8F0 !important;
        }
        input, select {
            background-color: #FFFFFF !important;
            border-color: #E2E8F0 !important;
            color: #1E293B !important;
        }
        input:focus, select:focus {
            border-color: #2563EB !important;
            box-shadow: 0 0 10px rgba(37, 99, 235, 0.2) !important;
        }

        /* Dark mode overrides */
        body.dark {
            background-color: #0B0F19 !important;
            color: #F9FAFB !important;
        }
        body.dark .bg-white {
            background-color: #1F2937 !important;
        }
        body.dark .border-slate-200 {
            border-color: #374151 !important;
        }
        body.dark .text-slate-800 {
            color: #F9FAFB !important;
        }
        body.dark .text-slate-655, body.dark .text-slate-600 {
            color: #9CA3AF !important;
        }
        body.dark .text-slate-500 {
            color: #9CA3AF !important;
        }
        body.dark input, body.dark select {
            background-color: #111827 !important;
            border-color: #374151 !important;
            color: #F9FAFB !important;
        }
        body.dark input::placeholder {
            color: #6B7280 !important;
        }
        body.dark a.text-blue-600 {
            color: #60a5fa !important;
        }
        body.dark a.text-blue-600:hover {
            color: #93c5fd !important;
        }
        body.dark .bg-rose-50 {
            background-color: rgba(220, 38, 38, 0.15) !important;
            border-color: rgba(220, 38, 38, 0.3) !important;
            color: #F87171 !important;
        }
        body.dark .brand-logo-light {
            display: none !important;
        }
        body.dark .brand-logo-dark {
            display: block !important;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans min-h-screen flex items-center justify-center p-4">
    <script>
        (function() {
            const theme = localStorage.getItem('theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (theme === 'dark' || (!theme && systemPrefersDark)) {
                document.body.classList.add('dark');
            }
        })();
    </script>
    <!-- Floating Theme Toggle -->
    <div class="absolute top-4 right-4 z-50">
        <button id="theme-toggle" class="p-2 rounded-lg bg-white border border-slate-200 text-slate-600 hover:text-blue-600 hover:bg-slate-50 transition-all shadow-sm flex items-center justify-center cursor-pointer" title="Ubah Tema">
            <i class="bi bi-moon-stars"></i>
        </button>
    </div>

    <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl p-8 shadow-sm relative overflow-hidden">
        <!-- Accent line -->
        <div class="absolute top-0 left-0 w-full h-1 bg-[#F59E0B]"></div>

        <div class="text-center mb-8">
            <img src="{{ asset('Logo.png') }}" alt="Logo PT Hariff" class="h-12 w-auto mx-auto mb-4 rounded-xl brand-logo-light">
            <img src="{{ asset('logo-darkmode.jpg') }}" alt="Logo PT Hariff" class="h-12 w-auto mx-auto mb-4 rounded-xl brand-logo-dark hidden">
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-800">Registrasi Akun</h1>
            <p class="text-xs text-slate-500 font-mono tracking-wider mt-1 uppercase">PT HARIFF STAFF ENROLLMENT</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                <div class="font-bold mb-1 flex items-center gap-1.5"><i class="bi bi-exclamation-triangle-fill"></i> REGISTRATION ERRORS:</div>
                <ul class="list-disc pl-4 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="name" class="block text-xs font-mono tracking-wider text-slate-600 uppercase mb-1.5">Nama Lengkap</label>
                <input type="text" id="name" name="name" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 text-sm focus:outline-none focus:border-blue-600 transition-all placeholder-slate-400" placeholder="Nama Lengkap Anda" value="{{ old('name') }}" required autofocus>
            </div>

            <div>
                <label for="email" class="block text-xs font-mono tracking-wider text-slate-600 uppercase mb-1.5">Alamat Email</label>
                <input type="email" id="email" name="email" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 text-sm focus:outline-none focus:border-blue-600 transition-all placeholder-slate-400" placeholder="email@hariff.co.id" value="{{ old('email') }}" required>
            </div>

            <div>
                <label for="role" class="block text-xs font-mono tracking-wider text-slate-600 uppercase mb-1.5">Peran / Otoritas Staf</label>
                <select id="role" name="role" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2.5 text-slate-800 text-sm focus:outline-none focus:border-blue-600 transition-all" required>
                    <option value="reporter" {{ old('role') === 'reporter' ? 'selected' : '' }}>Reporter (Operator Produksi/QA)</option>
                    <option value="mekanik" {{ old('role') === 'mekanik' ? 'selected' : '' }}>Mekanik (Teknisi Penanganan)</option>
                </select>
            </div>

            <div>
                <label for="password" class="block text-xs font-mono tracking-wider text-slate-600 uppercase mb-1.5">Kata Sandi</label>
                <input type="password" id="password" name="password" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 text-sm focus:outline-none focus:border-blue-600 transition-all placeholder-slate-400" placeholder="Minimal 6 karakter" required>
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-mono tracking-wider text-slate-600 uppercase mb-1.5">Konfirmasi Sandi</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 text-sm focus:outline-none focus:border-blue-600 transition-all placeholder-slate-400" placeholder="Ketik ulang kata sandi" required>
            </div>

            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 btn-premium-gradient rounded-lg text-sm font-bold shadow-sm transition-all transform active:scale-[0.98] mt-4">
                <i class="bi bi-person-plus-fill"></i> REGISTRASI AKUN
            </button>
        </form>

        <div class="text-center mt-6 text-xs font-mono tracking-wide text-slate-500">
            Sudah punya akun? <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-bold hover:underline">Masuk Sesi</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('theme-toggle');
            const body = document.body;

            function updateThemeUI(isDark) {
                const iconClass = isDark ? 'bi-sun' : 'bi-moon-stars';
                const titleText = isDark ? 'Ubah ke Mode Terang' : 'Ubah ke Mode Gelap';
                if (themeToggle) {
                    themeToggle.innerHTML = `<i class="bi ${iconClass}"></i>`;
                    themeToggle.title = titleText;
                }
            }

            const initialDark = body.classList.contains('dark');
            updateThemeUI(initialDark);

            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    const isDark = !body.classList.contains('dark');
                    body.classList.toggle('dark', isDark);
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                    updateThemeUI(isDark);
                });
            }
        });
    </script>
</body>
</html>
