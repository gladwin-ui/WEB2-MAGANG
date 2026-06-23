<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - BugTrack MFG</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body style="margin: 0; padding: 0;">
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <div class="brand" style="justify-content: center; margin-bottom: 0.5rem;">
                    <i class="bi bi-cpu-fill"></i>
                    <span>BugTrack MFG</span>
                </div>
                <div class="auth-title">Selamat Datang</div>
                <div class="auth-subtitle">Sistem Pelaporan & Penanganan Bug Manufaktur</div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div style="font-size: 0.85rem;">
                        @foreach ($errors->all() as $error)
                            {{ $error }}<br>
                        @endforeach
                    </div>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Kata Sandi</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 2rem;">
                    <input type="checkbox" id="remember" name="remember" style="width: auto;">
                    <label for="remember" class="form-label" style="margin-bottom: 0; cursor: pointer;">Ingat Saya</label>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk Sistem
                </button>
            </form>

            <div style="text-align: center; margin-top: 1.5rem; font-size: 0.85rem; color: var(--text-secondary);">
                Belum punya akun? <a href="{{ route('register') }}" style="color: var(--color-info); text-decoration: none; font-weight: 600;">Daftar di sini</a>
            </div>
        </div>
    </div>
</body>
</html>
