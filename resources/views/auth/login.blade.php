<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - BugTrack MFG</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        slate: {
                            950: '#F8FAFC',
                        }
                    },
                    fontFamily: {
                        mono: ['JetBrains Mono', 'Fira Code', 'Courier New', 'monospace'],
                        sans: ['Inter', 'Outfit', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* Force font override globally */
        body {
            font-family: 'Inter', 'Outfit', sans-serif !important;
        }
        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Outfit', sans-serif !important;
        }
        .font-mono, code {
            font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', monospace !important;
        }

        /* Global Color Theme Overrides */
        body {
            background-color: #F8FAFC !important;
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
        input {
            background-color: #FFFFFF !important;
            border-color: #E2E8F0 !important;
            color: #1E293B !important;
        }
        input:focus {
            border-color: #2563EB !important;
            box-shadow: 0 0 10px rgba(37, 99, 235, 0.2) !important;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl p-8 shadow-sm relative overflow-hidden">
        <!-- Accent line -->
        <div class="absolute top-0 left-0 w-full h-1 bg-blue-600"></div>

        <div class="text-center mb-8">
            <div class="inline-flex h-12 w-12 rounded-xl bg-blue-600 items-center justify-center shadow-sm mb-4">
                <i class="bi bi-cpu text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-800">BugTrack MFG</h1>
            <p class="text-xs text-slate-500 font-mono tracking-widest mt-1 uppercase">PT HARIFF SYSTEM ACCESS</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                <div class="font-bold mb-1 flex items-center gap-1.5"><i class="bi bi-exclamation-triangle-fill"></i> LOG IN FAILED:</div>
                <ul class="list-disc pl-4 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-xs font-mono tracking-wider text-slate-600 uppercase mb-2">Alamat Email / ID Staf</label>
                <input type="email" id="email" name="email" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2.5 text-slate-800 text-sm focus:outline-none focus:border-blue-600 transition-all placeholder-slate-400" placeholder="nama@hariff.co.id" value="{{ old('email') }}" required autofocus>
            </div>

            <div>
                <label for="password" class="block text-xs font-mono tracking-wider text-slate-600 uppercase mb-2">Kata Sandi / Security Key</label>
                <input type="password" id="password" name="password" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2.5 text-slate-800 text-sm focus:outline-none focus:border-blue-600 transition-all placeholder-slate-400" placeholder="••••••••" required>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" id="remember" name="remember" class="rounded bg-white border-slate-300 text-blue-600 focus:ring-0 focus:ring-offset-0">
                <label for="remember" class="text-xs text-slate-500 font-mono tracking-wider cursor-pointer uppercase select-none">Ingat Sesi Saya</label>
            </div>

            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold shadow-sm transition-all transform active:scale-[0.98]">
                <i class="bi bi-box-arrow-in-right"></i> OTENTIKASI & MASUK
            </button>
        </form>

        <div class="text-center mt-6 text-xs font-mono tracking-wide text-slate-500">
            Belum punya kredensial? <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-700 font-bold hover:underline">Registrasi Baru</a>
        </div>
    </div>
</body>
</html>
</body>
</html>
