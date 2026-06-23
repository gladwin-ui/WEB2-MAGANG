<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - BugTrack MFG</title>
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
                            950: '#0B0F19',
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
            background-color: #060814 !important;
        }
        .bg-slate-950 {
            background-color: #060814 !important;
        }
        .bg-slate-900\/60 {
            background-color: #111625 !important;
        }
        .border-slate-800, .border-slate-850 {
            border-color: #1F293D !important;
        }
        input, select {
            background-color: #060814 !important;
            border-color: #1F293D !important;
        }
        input:focus, select:focus {
            border-color: #00F0FF !important;
            box-shadow: 0 0 10px rgba(0, 240, 255, 0.2) !important;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 font-sans min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-slate-900/60 border border-slate-800 rounded-2xl p-8 shadow-2xl backdrop-blur-md relative overflow-hidden">
        <!-- Accent line -->
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 to-indigo-600"></div>

        <div class="text-center mb-8">
            <div class="inline-flex h-12 w-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 items-center justify-center shadow-lg shadow-indigo-500/20 mb-4">
                <i class="bi bi-cpu text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-100">Registrasi Akun</h1>
            <p class="text-xs text-slate-400 font-mono tracking-widest mt-1 uppercase">PT HARIFF STAFF ENROLLMENT</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs">
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
                <label for="name" class="block text-xs font-mono tracking-wider text-slate-450 uppercase mb-1.5">Nama Lengkap</label>
                <input type="text" id="name" name="name" class="w-full bg-slate-950 border border-slate-850 rounded-lg px-4 py-2 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition-all placeholder-slate-800" placeholder="Nama Lengkap Anda" value="{{ old('name') }}" required autofocus>
            </div>

            <div>
                <label for="email" class="block text-xs font-mono tracking-wider text-slate-450 uppercase mb-1.5">Alamat Email</label>
                <input type="email" id="email" name="email" class="w-full bg-slate-950 border border-slate-850 rounded-lg px-4 py-2 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition-all placeholder-slate-800" placeholder="email@hariff.co.id" value="{{ old('email') }}" required>
            </div>

            <div>
                <label for="role" class="block text-xs font-mono tracking-wider text-slate-450 uppercase mb-1.5">Peran / Otoritas Staf</label>
                <select id="role" name="role" class="w-full bg-slate-950 border border-slate-850 rounded-lg px-4 py-2 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition-all" required>
                    <option value="reporter" {{ old('role') === 'reporter' ? 'selected' : '' }}>Reporter (Operator Produksi/QA)</option>
                    <option value="mekanik" {{ old('role') === 'mekanik' ? 'selected' : '' }}>Mekanik (Teknisi Penanganan)</option>
                </select>
            </div>

            <div>
                <label for="password" class="block text-xs font-mono tracking-wider text-slate-450 uppercase mb-1.5">Kata Sandi</label>
                <input type="password" id="password" name="password" class="w-full bg-slate-950 border border-slate-850 rounded-lg px-4 py-2 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition-all placeholder-slate-800" placeholder="Minimal 6 karakter" required>
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-mono tracking-wider text-slate-450 uppercase mb-1.5">Konfirmasi Sandi</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="w-full bg-slate-950 border border-slate-850 rounded-lg px-4 py-2 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition-all placeholder-slate-800" placeholder="Ketik ulang kata sandi" required>
            </div>

            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-750 text-white rounded-lg text-sm font-bold shadow-lg shadow-indigo-500/10 transition-all transform active:scale-[0.98] mt-4">
                <i class="bi bi-person-plus-fill"></i> REGISTRASI AKUN
            </button>
        </form>

        <div class="text-center mt-6 text-xs font-mono tracking-wide text-slate-500">
            Sudah punya akun? <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 font-bold hover:underline">Masuk Sesi</a>
        </div>
    </div>
</body>
</html>
