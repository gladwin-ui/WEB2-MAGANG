<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — ManufakTrack</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
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
                        'accent-soft': 'var(--color-accent-soft)'
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
         * OFFICIAL HARIFF DEFENSE DESIGN SYSTEM TOKENS
         * ============================================================ */
        :root {
            --color-bg-primary:    #F6FAFD;
            --color-bg-secondary:  #FFFFFF;
            --color-bg-tertiary:   #EAF2F9;
            --color-text-primary:   #0A1931;
            --color-text-secondary: #1A3D63;
            --color-text-muted:     #4A7FA7;
            --color-border:         #D5E3F0;
            --color-border-strong:  #B3CFE5;
            --color-accent:         #1A3D63;
            --color-accent-hover:   #0A1931;
            --color-accent-soft:    #4A7FA7;
        }

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
        }

        /* Base Typography & Transitions */
        body {
            font-family: 'Inter', sans-serif !important;
            background-color: var(--color-bg-primary) !important;
            color: var(--color-text-primary) !important;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif !important;
            font-weight: 800 !important;
        }
        .font-mono {
            font-family: 'Inter', sans-serif !important;
        }

        /* Input styling overrides */
        input[type="email"], input[type="password"], input[type="text"] {
            background-color: var(--color-bg-secondary) !important;
            border-color: var(--color-border) !important;
            color: var(--color-text-primary) !important;
            transition: all 0.2s ease;
        }
        input[type="email"]:focus, input[type="password"]:focus, input[type="text"]:focus {
            border-color: #3B82F6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
            outline: none !important;
        }
        input::placeholder {
            color: var(--color-text-muted) !important;
            opacity: 0.6;
        }

        body.dark .brand-logo-light {
            display: none !important;
        }
        body.dark .brand-logo-dark {
            display: block !important;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-x-hidden">
    <!-- Theme Auto-Detect Script -->
    <script>
        (function() {
            const theme = localStorage.getItem('theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (theme === 'dark' || (!theme && systemPrefersDark)) {
                document.body.classList.add('dark');
            }
        })();
    </script>

    <!-- Ambient Modern Background Glows -->
    <div class="fixed top-[-10%] left-[-10%] w-[45vw] h-[45vw] rounded-full bg-blue-500/10 dark:bg-blue-500/15 blur-[120px] pointer-events-none -z-10 animate-pulse" style="animation-duration: 8s;"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-[45vw] h-[45vw] rounded-full bg-indigo-500/10 dark:bg-indigo-500/15 blur-[120px] pointer-events-none -z-10 animate-pulse" style="animation-duration: 10s;"></div>

    <!-- Floating Theme Toggle -->
    <div class="absolute top-6 right-6 z-50">
        <button id="theme-toggle" class="w-11 h-11 rounded-xl bg-bg-secondary border border-border-default text-text-secondary hover:text-accent hover:border-accent shadow-sm flex items-center justify-center transition-all duration-200 cursor-pointer transform hover:scale-105 active:scale-95" title="Ubah Tema">
            <i class="bi bi-moon-stars text-lg"></i>
        </button>
    </div>

    <!-- Main Authentication Card -->
    <div class="w-full max-w-md bg-bg-secondary/95 border border-border-default/80 rounded-3xl p-8 sm:p-10 shadow-2xl relative z-10 overflow-hidden backdrop-blur-xl transition-all duration-300">
        <!-- Modern 3-Color Brand Accent Bar -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-blue-600 via-indigo-500 to-emerald-500"></div>

        <!-- Logo & Header Section -->
        <div class="text-center mb-8 pt-2">
            <div class="w-20 h-20 flex items-center justify-center mx-auto mb-3">
                <img src="<?php echo e(asset('LOGO LOGO LAGI.png')); ?>" alt="Logo Hariff Defense" class="w-full h-full object-contain brand-logo-light">
                <img src="<?php echo e(asset('LOGO LOGO LAGI.png')); ?>" alt="Logo Hariff Defense" class="w-full h-full object-contain brand-logo-dark hidden">
            </div>
            <h1 class="text-2xl font-black tracking-tight text-text-primary">ManufakTrack</h1>
            <p class="text-xs font-mono tracking-widest text-accent mt-1.5 font-bold">By Hariff Defense</p>
        </div>

        <!-- Error Notification Banner -->
        <?php if($errors->any()): ?>
            <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-600 dark:text-rose-400 text-xs shadow-sm">
                <div class="font-bold mb-1.5 flex items-center gap-2 text-sm">
                    <i class="bi bi-exclamation-triangle-fill text-rose-500"></i> OTENTIKASI GAGAL:
                </div>
                <ul class="list-disc pl-5 space-y-0.5">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="<?php echo e(route('login')); ?>" method="POST" class="space-y-5">
            <?php echo csrf_field(); ?>
            <div>
                <label for="email" class="flex items-center gap-1.5 text-xs font-bold text-text-secondary uppercase tracking-wider mb-2">
                    <i class="bi bi-person-badge text-accent text-sm"></i> Alamat Email / ID Staf
                </label>
                <input type="email" id="email" name="email" class="w-full rounded-xl px-4 py-3 text-sm font-medium transition-all shadow-inner" placeholder="nama@hariff.co.id" value="<?php echo e(old('email')); ?>" required autofocus>
            </div>

            <div>
                <label for="password" class="flex items-center gap-1.5 text-xs font-bold text-text-secondary uppercase tracking-wider mb-2">
                    <i class="bi bi-key-fill text-accent text-sm"></i> Kata Sandi / Security Key
                </label>
                <input type="password" id="password" name="password" class="w-full rounded-xl px-4 py-3 text-sm font-medium transition-all shadow-inner" placeholder="••••••••" required>
            </div>

            <div class="flex items-center justify-between pt-1">
                <div class="flex items-center gap-2.5">
                    <input type="checkbox" id="remember" name="remember" class="w-4 h-4 rounded bg-bg-secondary border-border-default text-blue-600 focus:ring-0 focus:ring-offset-0 cursor-pointer">
                    <label for="remember" class="text-xs text-text-secondary font-medium tracking-wide cursor-pointer select-none">Ingat Sesi Saya</label>
                </div>
            </div>

            <button type="submit" class="w-full flex items-center justify-center gap-2.5 px-6 py-3.5 bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transform hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 cursor-pointer pt-3.5 pb-3.5 mt-2">
                <i class="bi bi-shield-lock-fill text-base"></i> OTENTIKASI & MASUK
            </button>
        </form>

        <!-- Footer Link -->
        <div class="mt-8 pt-6 border-t border-border-default/60 text-center text-xs text-text-secondary">
            Belum punya kredensial? 
            <a href="<?php echo e(route('register')); ?>" class="text-blue-600 hover:text-blue-500 dark:text-blue-400 font-bold hover:underline inline-flex items-center gap-1 ml-1 transition-colors">
                Registrasi Baru <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Theme Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('theme-toggle');
            const body = document.body;

            function updateThemeUI(isDark) {
                const iconClass = isDark ? 'bi-sun-fill text-yellow-400' : 'bi-moon-stars-fill text-blue-600';
                const titleText = isDark ? 'Ubah ke Mode Terang' : 'Ubah ke Mode Gelap';
                if (themeToggle) {
                    themeToggle.innerHTML = `<i class="bi ${iconClass} transition-transform duration-300"></i>`;
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
<?php /**PATH D:\MAGANG\WEB2-MAGANG\resources\views/auth/login.blade.php ENDPATH**/ ?>