<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - BugTrack MFG</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body style="margin: 0; padding: 0;">
    <div class="auth-wrapper">
        <div class="auth-card" style="max-width: 480px;">
            <div class="auth-header">
                <div class="brand" style="justify-content: center; margin-bottom: 0.5rem;">
                    <i class="bi bi-cpu-fill"></i>
                    <span>BugTrack MFG</span>
                </div>
                <div class="auth-title">Daftar Akun Baru</div>
                <div class="auth-subtitle">Gabung untuk melaporkan atau menangani kerusakan produksi</div>
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

            <form action="{{ route('register') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Nama Anda" value="{{ old('name') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" value="{{ old('email') }}" required>
                </div>

                <div class="form-group">
                    <label for="role" class="form-label">Peran Pengguna (Role)</label>
                    <select id="role" name="role" required>
                        <option value="reporter" {{ old('role') === 'reporter' ? 'selected' : '' }}>Reporter (Operator Produksi/QA)</option>
                        <option value="mekanik" {{ old('role') === 'mekanik' ? 'selected' : '' }}>Mekanik (Teknisi Penanganan)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Kata Sandi</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Ketik ulang kata sandi" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 1.5rem;">
                    <i class="bi bi-person-plus-fill"></i> Buat Akun & Masuk
                </button>
            </form>

            <div style="text-align: center; margin-top: 1.5rem; font-size: 0.85rem; color: var(--text-secondary);">
                Sudah punya akun? <a href="{{ route('login') }}" style="color: var(--color-info); text-decoration: none; font-weight: 600;">Masuk di sini</a>
            </div>
        </div>
    </div>
</body>
</html>
