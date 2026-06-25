@extends('layouts.app')

@section('title', 'Pengaturan Akun')

@section('content')
@php
    $user = auth()->user();
    $profilePhotoUrl = $user->profile_photo_url;
    $profileInitials = strtoupper(substr($user->name ?? '', 0, 2));
@endphp
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
            <input type="hidden" name="remove_photo" id="remove_photo" value="0">

            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="shrink-0">
                    <label
                        for="profile_photo"
                        class="group relative h-20 w-20 rounded-full overflow-hidden border border-slate-200 bg-slate-100 flex items-center justify-center cursor-pointer shadow-sm"
                        title="Klik untuk menambah atau mengganti foto profil"
                    >
                        <img
                            id="profile-photo-preview"
                            @if($profilePhotoUrl)src="{{ $profilePhotoUrl }}"@endif
                            alt="Foto Profil"
                            class="h-full w-full object-cover {{ $profilePhotoUrl ? '' : 'hidden' }}"
                        >
                        <span
                            id="profile-photo-fallback"
                            class="text-xs font-bold text-slate-700 text-center px-2 {{ $profilePhotoUrl ? 'hidden' : '' }}"
                        >
                            <span class="block text-lg leading-none">+</span>
                            <span class="block mt-0.5">Tambah</span>
                        </span>
                        <span class="absolute inset-0 flex items-center justify-center bg-slate-900/0 group-hover:bg-slate-900/20 transition-all">
                            <span class="sr-only">Pilih foto profil</span>
                        </span>
                    </label>
                    <input
                        id="profile_photo"
                        type="file"
                        name="profile_photo"
                        accept=".jpg,.jpeg,.png,.webp"
                        class="sr-only"
                    >
                </div>
                <div class="w-full space-y-2">
                    <div>
                        <p class="block text-xs font-bold text-slate-600 font-mono uppercase tracking-wider">
                            Foto Profil
                        </p>
                        <p class="text-xs text-slate-500 mt-1">
                            Klik lingkaran untuk tambah atau ganti foto.
                        </p>
                    </div>
                    @if($profilePhotoUrl)
                        <button
                            type="button"
                            id="remove-photo-button"
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-red-200 bg-red-50 text-red-700 text-sm font-semibold hover:bg-red-100 transition-all"
                        >
                            <i class="bi bi-trash"></i> Hapus Foto Profil
                        </button>
                    @endif
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

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('profile_photo');
        const preview = document.getElementById('profile-photo-preview');
        const fallback = document.getElementById('profile-photo-fallback');
        const removePhoto = document.getElementById('remove_photo');
        const removeButton = document.getElementById('remove-photo-button');

        if (!input || !preview || !fallback) {
            return;
        }

        const form = input.closest('form');

        const originalSrc = preview.getAttribute('src');
        let objectUrl = null;

        input.addEventListener('change', function () {
            const file = input.files && input.files[0];

            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }

            if (file) {
                objectUrl = URL.createObjectURL(file);
                preview.src = objectUrl;
                preview.classList.remove('hidden');
                fallback.classList.add('hidden');
                if (removePhoto) {
                    removePhoto.value = '0';
                }
                return;
            }

            if (originalSrc) {
                preview.src = originalSrc;
                preview.classList.remove('hidden');
                fallback.classList.add('hidden');
                return;
            }

            preview.classList.add('hidden');
            fallback.classList.remove('hidden');
        });

        if (removeButton && form && removePhoto) {
            removeButton.addEventListener('click', function () {
                removePhoto.value = '1';
                form.requestSubmit();
            });
        }
    });
</script>
@endsection
