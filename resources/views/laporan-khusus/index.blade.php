@extends('layouts.app')

@section('title', 'Laporan Khusus')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-extrabold text-text-primary tracking-tight mb-2">Laporan Khusus</h1>
    <p class="text-sm text-text-muted mb-8">Halaman ini sedang dalam pengembangan.</p>

    {{-- Placeholder empty state --}}
    <div class="flex flex-col items-center justify-center py-24 text-center bg-bg-secondary border-2 border-dashed border-border-default rounded-xl">
        <div class="w-14 h-14 rounded-2xl bg-bg-tertiary flex items-center justify-center mb-4">
            <i class="bi bi-file-earmark-text text-2xl text-accent-soft"></i>
        </div>
        <h3 class="text-text-primary font-bold text-base">Segera Hadir</h3>
        <p class="text-text-muted text-sm mt-1 max-w-sm">Fitur Laporan Khusus akan tersedia di update berikutnya.</p>
    </div>
</div>
@endsection
