<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ManufakTrack — Hariff Defense Manufacturing AI Bug Analytics</title>
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
                        'accent': '#1A3D63',
                        'accent-hover': '#132C48',
                        'accent-blue': '#2563EB'
                    },
                    fontFamily: {
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
            --color-bg-primary: #F8FAFC;
            --color-bg-secondary: #FFFFFF;
            --color-bg-tertiary: #F1F5F9;
            --color-text-primary: #0F172A;
            --color-text-secondary: #334155;
            --color-text-muted: #64748B;
            --color-border: #E2E8F0;
        }
        body.dark {
            --color-bg-primary: #0B1120;
            --color-bg-secondary: #111827;
            --color-bg-tertiary: #1E293B;
            --color-text-primary: #F8FAFC;
            --color-text-secondary: #CBD5E1;
            --color-text-muted: #94A3B8;
            --color-border: #334155;
        }
        body {
            background-color: var(--color-bg-primary);
            color: var(--color-text-primary);
            font-family: 'Inter', sans-serif;
        }
        h1, h2, h3, h4 {
            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col transition-colors duration-300">

    <!-- Navigation Header -->
    <header class="sticky top-0 z-50 bg-bg-secondary/90 backdrop-blur-md border-b border-border-default transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('LOGO LOGO LAGI.png') }}" alt="Hariff Defense" class="h-8 w-auto object-contain">
                <div>
                    <span class="text-base font-black tracking-tight text-text-primary">ManufakTrack</span>
                    <span class="block text-[9px] tracking-widest text-accent-blue font-bold uppercase">PT HARIFF DIPA PERSADA</span>
                </div>
            </div>

            <nav class="hidden md:flex items-center gap-8 text-xs font-semibold text-text-secondary">
                <a href="#fitur" class="hover:text-accent-blue transition-colors">Fitur Utama</a>
                <a href="#arsitektur" class="hover:text-accent-blue transition-colors">Teknologi AI</a>
                <a href="#integrasi" class="hover:text-accent-blue transition-colors">Alur Pelaporan</a>
            </nav>

            <div class="flex items-center gap-3">
                <!-- Theme Toggle -->
                <button id="theme-toggle" class="w-9 h-9 rounded-xl bg-bg-tertiary border border-border-default text-text-secondary hover:text-accent-blue flex items-center justify-center transition-all cursor-pointer" title="Ubah Tema">
                    <i class="bi bi-moon-stars"></i>
                </button>

                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-accent-blue hover:bg-blue-700 transition-all shadow-sm">
                        <i class="bi bi-speedometer2"></i> Masuk Dasbor
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-accent-blue hover:bg-blue-700 transition-all shadow-sm">
                        <i class="bi bi-box-arrow-in-right"></i> Portal Masuk
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative overflow-hidden py-16 lg:py-24 border-b border-border-default">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left Content -->
                <div class="lg:col-span-7 space-y-6 text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-500/10 border border-blue-500/20 text-accent-blue text-xs font-bold">
                        <i class="bi bi-shield-check"></i> Enterprise Manufacturing Quality Portal
                    </div>

                    <h1 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight text-text-primary">
                        Sistem Kendali & Analisis AI Kendala Produksi <span class="text-accent-blue">Manufaktur Pertahanan</span>
                    </h1>

                    <p class="text-sm sm:text-base text-text-secondary leading-relaxed max-w-2xl">
                        <strong>ManufakTrack</strong> memonitor seluruh gangguan perakitan perangkat elektronik PT Hariff Dipa Persada. Dilengkapi diagnosis sentimen AI dan pengelompokan masalah otomatis (NLP Overlap Clustering) untuk keputusan engineering yang presisi.
                    </p>

                    <div class="flex flex-wrap items-center gap-4 pt-2">
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl text-sm font-bold text-white bg-accent-blue hover:bg-blue-700 shadow-md transition-all">
                            <i class="bi bi-box-arrow-in-right"></i> Masuk Portal Staf
                        </a>
                        <a href="#fitur" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl text-sm font-bold text-text-secondary bg-bg-secondary border border-border-default hover:bg-bg-tertiary transition-all">
                            Jelajahi Fitur <i class="bi bi-arrow-down"></i>
                        </a>
                    </div>

                    <!-- Highlight Badges -->
                    <div class="grid grid-cols-3 gap-4 pt-6 border-t border-border-default">
                        <div>
                            <span class="block text-xl font-black text-text-primary">Realtime</span>
                            <span class="text-xs text-text-muted">Rework & Defect Rate</span>
                        </div>
                        <div>
                            <span class="block text-xl font-black text-text-primary">NLP AI</span>
                            <span class="text-xs text-text-muted">Auto-Clarity Clustering</span>
                        </div>
                        <div>
                            <span class="block text-xl font-black text-text-primary">100% Audit</span>
                            <span class="text-xs text-text-muted">Traceable SQL ETL</span>
                        </div>
                    </div>
                </div>

                <!-- Right Visual Showcase: Interactive Slide Photo Carousel -->
                <div class="lg:col-span-5">
                    <div class="relative rounded-2xl overflow-hidden border border-border-default shadow-2xl bg-bg-secondary group">
                        <div id="hero-carousel" class="aspect-video sm:aspect-square relative overflow-hidden">
                            <!-- Slide 0 -->
                            <div class="carousel-slide absolute inset-0 transition-opacity duration-700 ease-in-out opacity-100" data-slide="0">
                                <img src="{{ asset('dok/BMC BMS.jpeg') }}" alt="BMC BMS Hariff" class="w-full h-full object-cover">
                            </div>

                            <!-- Slide 1 -->
                            <div class="carousel-slide absolute inset-0 transition-opacity duration-700 ease-in-out opacity-0 pointer-events-none" data-slide="1">
                                <img src="{{ asset('dok/RKAP 2026.jpg') }}" alt="Kegiatan Manufaktur Hariff" class="w-full h-full object-cover">
                            </div>

                            <!-- Slide 2 -->
                            <div class="carousel-slide absolute inset-0 transition-opacity duration-700 ease-in-out opacity-0 pointer-events-none" data-slide="2">
                                <img src="{{ asset('dok/WhatsApp Image 2025-06-17 at 10.04.02 AM copy.jpg') }}" alt="Pemeriksaan Quality Control" class="w-full h-full object-cover">
                            </div>

                            <!-- Slide 3 -->
                            <div class="carousel-slide absolute inset-0 transition-opacity duration-700 ease-in-out opacity-0 pointer-events-none" data-slide="3">
                                <img src="{{ asset('dok/WhatsApp Image 2025-06-17 at 10.04.02 AM-2 copy.jpg') }}" alt="Jalur Perakitan Hariff" class="w-full h-full object-cover">
                            </div>

                            <!-- Carousel Navigation Arrows -->
                            <button id="carousel-prev" class="absolute top-1/2 left-3 -translate-y-1/2 w-9 h-9 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 shadow-md cursor-pointer z-10" title="Foto Sebelumnya">
                                <i class="bi bi-chevron-left text-sm"></i>
                            </button>
                            <button id="carousel-next" class="absolute top-1/2 right-3 -translate-y-1/2 w-9 h-9 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 shadow-md cursor-pointer z-10" title="Foto Berikutnya">
                                <i class="bi bi-chevron-right text-sm"></i>
                            </button>

                            <!-- Carousel Dots Indicator -->
                            <div class="absolute top-4 right-4 flex items-center gap-1.5 bg-black/40 backdrop-blur-sm px-2.5 py-1.5 rounded-full z-10">
                                <button class="carousel-dot w-4 h-2 rounded-full bg-white transition-all duration-300" data-idx="0"></button>
                                <button class="carousel-dot w-2 h-2 rounded-full bg-white/40 hover:bg-white/80 transition-all duration-300" data-idx="1"></button>
                                <button class="carousel-dot w-2 h-2 rounded-full bg-white/40 hover:bg-white/80 transition-all duration-300" data-idx="2"></button>
                                <button class="carousel-dot w-2 h-2 rounded-full bg-white/40 hover:bg-white/80 transition-all duration-300" data-idx="3"></button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Fitur Utama Grid Section -->
    <section id="fitur" class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <span class="text-xs font-bold text-accent-blue uppercase tracking-widest">Kapabilitas Sistem</span>
                <h2 class="text-2xl sm:text-4xl font-black text-text-primary mt-2">
                    Fitur Lengkap Portal ManufakTrack
                </h2>
                <p class="text-sm text-text-muted mt-3">
                    Dirancang khusus untuk mempermudah koordinasi antara tim produksi lantai pabrik (assembly line) dan tim Quality & Engineering.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Fitur 1 -->
                <div class="p-6 rounded-2xl bg-bg-secondary border border-border-default hover:border-accent-blue transition-all shadow-sm">
                    <div class="w-11 h-11 rounded-xl bg-blue-500/10 text-accent-blue flex items-center justify-center text-xl mb-4 font-bold">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <h3 class="text-base font-bold text-text-primary mb-2">Dasbor Eksekutif & KPI Produksi</h3>
                    <p class="text-xs text-text-secondary leading-relaxed">
                        Pantau metrik vital pabrik seperti persentase Rework Rate, distribusi keparahan masalah (Critical, Major, Minor), serta grafik tren laporan 7 hari terakhir secara interaktif dengan ApexCharts.
                    </p>
                </div>

                <!-- Fitur 2 -->
                <div class="p-6 rounded-2xl bg-bg-secondary border border-border-default hover:border-accent-blue transition-all shadow-sm">
                    <div class="w-11 h-11 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center text-xl mb-4 font-bold">
                        <i class="bi bi-cpu"></i>
                    </div>
                    <h3 class="text-base font-bold text-text-primary mb-2">Diagnosis Sentimen & Urgency AI</h3>
                    <p class="text-xs text-text-secondary leading-relaxed">
                        Laporan teknis dianalisis otomatis oleh mikroservis Python FastAPI untuk menilai bobot kedaruratan (Urgency Score), membantu tim engineer memprioritaskan kendala paling kritis di jalur perakitan.
                    </p>
                </div>

                <!-- Fitur 3 -->
                <div class="p-6 rounded-2xl bg-bg-secondary border border-border-default hover:border-accent-blue transition-all shadow-sm">
                    <div class="w-11 h-11 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-xl mb-4 font-bold">
                        <i class="bi bi-diagram-3"></i>
                    </div>
                    <h3 class="text-base font-bold text-text-primary mb-2">Clustering Gejala & Akar Penyebab</h3>
                    <p class="text-xs text-text-secondary leading-relaxed">
                        Algoritma NLP Connected Components (Overlap minimal 2 Kata Berulang) mengelompokkan laporan bersinonim secara cerdas ke dalam Top 5 Masalah Tersering dan Top 5 Root Cause Dominan anti-duplikasi.
                    </p>
                </div>

                <!-- Fitur 4 -->
                <div class="p-6 rounded-2xl bg-bg-secondary border border-border-default hover:border-accent-blue transition-all shadow-sm">
                    <div class="w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-xl mb-4 font-bold">
                        <i class="bi bi-database-check"></i>
                    </div>
                    <h3 class="text-base font-bold text-text-primary mb-2">Impor Data SQL & ETL Terstruktur</h3>
                    <p class="text-xs text-text-secondary leading-relaxed">
                        Sistem mendukung unggah berkas SQL produksi dengan pemrosesan antrean background job, pemantauan progress bar waktu nyata, serta riwayat audit trail lengkap yang bisa dipulihkan.
                    </p>
                </div>

                <!-- Fitur 5 -->
                <div class="p-6 rounded-2xl bg-bg-secondary border border-border-default hover:border-accent-blue transition-all shadow-sm">
                    <div class="w-11 h-11 rounded-xl bg-purple-500/10 text-purple-600 flex items-center justify-center text-xl mb-4 font-bold">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                    </div>
                    <h3 class="text-base font-bold text-text-primary mb-2">Ekspor Laporan Multi-Sheet XLSX</h3>
                    <p class="text-xs text-text-secondary leading-relaxed">
                        Unduh laporan kendala per proyek maupun rekap Laporan Khusus siap cetak dalam format Microsoft Excel berpenataan profesional untuk keperluan audit atau presentasi manajemen.
                    </p>
                </div>

                <!-- Fitur 6 -->
                <div class="p-6 rounded-2xl bg-bg-secondary border border-border-default hover:border-accent-blue transition-all shadow-sm">
                    <div class="w-11 h-11 rounded-xl bg-cyan-500/10 text-cyan-600 flex items-center justify-center text-xl mb-4 font-bold">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h3 class="text-base font-bold text-text-primary mb-2">Dukungan Mode Read-Only (VIEW)</h3>
                    <p class="text-xs text-text-secondary leading-relaxed">
                        Kompatibel penuh dengan arsitektur database read-only (MySQL VIEW). Sistem dengan cerdas beradaptasi menampilkan seluruh analitik secara resilient tanpa risiko error write.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Alur Kerja / Arsitektur Section -->
    <section id="arsitektur" class="py-16 bg-bg-tertiary border-y border-border-default">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                <div class="lg:col-span-6 space-y-4">
                    <span class="text-xs font-bold text-accent-blue uppercase tracking-widest">Alur Koordinasi</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-text-primary">
                        Jembatan Cepat Lantai Produksi ke Tim Engineering
                    </h2>
                    <p class="text-xs sm:text-sm text-text-secondary leading-relaxed">
                        Setiap kali teknisi mendapati kegagalan fungsi atau cacat perakitan pada produk elektronik pertahanan, laporan diklasifikasikan secara real-time untuk mempercepat waktu penanganan (Mean Time to Resolution).
                    </p>

                    <div class="space-y-3 pt-2">
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-accent-blue text-white flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">1</div>
                            <p class="text-xs text-text-secondary"><strong>Input Laporan Kendala:</strong> Teknisi memasukkan detail serial number, versi, dan langkah reproduksi kendala.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-accent-blue text-white flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">2</div>
                            <p class="text-xs text-text-secondary"><strong>Analisis NLP Python:</strong> Mikroservis mengekstrak kata kunci teknis dan menstandarkan sinonim kerusakan.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-accent-blue text-white flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">3</div>
                            <p class="text-xs text-text-secondary"><strong>Aksi Perbaikan & Audit:</strong> Manajer Quality memantau tren Rework Rate per proyek dan mengambil keputusan korektif.</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-6">
                    <div class="p-6 rounded-2xl bg-bg-secondary border border-border-default shadow-lg space-y-4">
                        <div class="flex items-center justify-between border-b border-border-default pb-3">
                            <span class="text-xs font-bold text-text-primary"><i class="bi bi-terminal mr-1"></i> Arsitektur Mikroservis</span>
                            <span class="text-[10px] font-mono text-text-muted">Laravel 13 + FastAPI Python</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3 text-center">
                            <div class="p-4 rounded-xl bg-bg-tertiary border border-border-default">
                                <i class="bi bi-server text-2xl text-accent-blue mb-1 block"></i>
                                <span class="text-xs font-bold text-text-primary block">Web Utama</span>
                                <span class="text-[10px] text-text-muted">Laravel Engine</span>
                            </div>
                            <div class="p-4 rounded-xl bg-bg-tertiary border border-border-default">
                                <i class="bi bi-cpu text-2xl text-indigo-500 mb-1 block"></i>
                                <span class="text-xs font-bold text-text-primary block">AI Service</span>
                                <span class="text-[10px] text-text-muted">FastAPI NLP Cluster</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="mt-auto py-8 border-t border-border-default bg-bg-secondary text-center text-xs text-text-muted">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <img src="{{ asset('LOGO LOGO LAGI.png') }}" alt="Hariff" class="h-6 w-auto">
                <span class="font-bold text-text-primary">PT Hariff Dipa Persada</span>
            </div>
            <p>&copy; {{ date('Y') }} ManufakTrack Portal. All rights reserved.</p>
        </div>
    </footer>

    <!-- Script Tema -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('theme-toggle');
            const body = document.body;
            function updateIcon(isDark) {
                if(btn) btn.innerHTML = isDark ? '<i class="bi bi-sun text-yellow-400"></i>' : '<i class="bi bi-moon-stars"></i>';
            }
            updateIcon(body.classList.contains('dark'));
            if(btn) {
                btn.addEventListener('click', () => {
                    const dark = !body.classList.contains('dark');
                    body.classList.toggle('dark', dark);
                    localStorage.setItem('theme', dark ? 'dark' : 'light');
                    updateIcon(dark);
                });
            }

            // Carousel script
            const slides = document.querySelectorAll('.carousel-slide');
            const dots = document.querySelectorAll('.carousel-dot');
            const prevBtn = document.getElementById('carousel-prev');
            const nextBtn = document.getElementById('carousel-next');
            let currentSlide = 0;
            let slideInterval;

            function showSlide(index) {
                if (!slides.length) return;
                slides.forEach((slide, i) => {
                    if (i === index) {
                        slide.classList.remove('opacity-0', 'pointer-events-none');
                        slide.classList.add('opacity-100');
                    } else {
                        slide.classList.remove('opacity-100');
                        slide.classList.add('opacity-0', 'pointer-events-none');
                    }
                });
                dots.forEach((dot, i) => {
                    if (i === index) {
                        dot.classList.remove('bg-white/40', 'w-2');
                        dot.classList.add('bg-white', 'w-4');
                    } else {
                        dot.classList.remove('bg-white', 'w-4');
                        dot.classList.add('bg-white/40', 'w-2');
                    }
                });
                currentSlide = index;
            }

            function nextSlide() {
                showSlide((currentSlide + 1) % slides.length);
            }

            function prevSlide() {
                showSlide((currentSlide - 1 + slides.length) % slides.length);
            }

            function startTimer() {
                clearInterval(slideInterval);
                slideInterval = setInterval(nextSlide, 3000);
            }

            if (slides.length > 0) {
                showSlide(0);
                startTimer();

                if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); startTimer(); });
                if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); startTimer(); });
                dots.forEach((dot, i) => {
                    dot.addEventListener('click', () => { showSlide(i); startTimer(); });
                });
            }
        });
    </script>
</body>
</html>
