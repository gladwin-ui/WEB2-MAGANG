@extends('layouts.app')

@section('title', 'Pengaturan Akun')

@section('content')
<div class="space-y-6 max-w-3xl">
    <div>
        <h1 class="text-2xl font-black text-slate-800 tracking-tight uppercase">Pengaturan Akun</h1>
        <p class="text-xs text-slate-500 font-mono tracking-wider uppercase mt-1">
            Kelola profil reporter untuk verifikasi akun
        </p>
    </div>

    <div class="premium-card premium-card-accent p-6">
        <form action="{{ route('users.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="flex items-center gap-4">
                <div class="h-16 w-16 rounded-full overflow-hidden border border-slate-200 bg-slate-100 flex items-center justify-center shrink-0">
                    @if(auth()->user()->profile_photo_path)
                        <img src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}" alt="Foto Profil" class="h-full w-full object-cover">
                    @else
                        <span class="text-sm font-bold text-slate-700">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </span>
                    @endif
                </div>
                <div class="w-full">
                    <label for="profile_photo" class="block text-xs font-bold text-slate-600 font-mono uppercase tracking-wider mb-2">
                        Ubah Foto Profil
                    </label>
                    <input id="profile_photo" type="file" name="profile_photo" accept=".jpg,.jpeg,.png,.webp"
                           class="w-full text-sm border border-slate-200 rounded px-3 py-2 bg-white text-slate-700">
                    @error('profile_photo')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="name" class="block text-xs font-bold text-slate-600 font-mono uppercase tracking-wider mb-2">
                    Username
                </label>
                <input id="name" type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required
                       class="w-full border border-slate-200 rounded px-3 py-2.5 bg-white text-slate-800 focus:outline-none focus:border-blue-600">
                @error('name')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="block text-xs font-bold text-slate-600 font-mono uppercase tracking-wider mb-2">
                    Nomor Telepon (Wajib Verifikasi)
                </label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', auth()->user()->phone) }}" required
                       placeholder="Contoh: 081234567890"
                       class="w-full border border-slate-200 rounded px-3 py-2.5 bg-white text-slate-800 focus:outline-none focus:border-blue-600">
                <p class="text-[11px] text-slate-500 mt-1">Nomor telepon wajib diisi sebagai syarat verifikasi akun.</p>
                @error('phone')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-2 border-t border-slate-200">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 btn-premium-gradient rounded-lg text-sm font-bold shadow-sm transition-all active:scale-[0.98]">
                    <i class="bi bi-save"></i> Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
