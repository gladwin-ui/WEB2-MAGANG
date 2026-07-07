@extends('layouts.app')

@section('title', 'Kelola Master Device')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 mb-6 border-b border-border-strong">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-bg-secondary border border-border-default flex items-center justify-center shrink-0 shadow-sm">
                <i class="bi bi-cpu-fill text-xl text-accent"></i>
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-black text-text-primary tracking-tight">Kelola Master Device</h1>
                <p class="text-xs md:text-sm text-text-secondary mt-0.5 font-medium">Tambahkan atau perbarui referensi data device Hariff Defense</p>
            </div>
        </div>
    </div>

    <!-- Layout Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Add Device (Left Panel) -->
        <div>
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <h2 class="text-xs font-mono tracking-widest text-slate-600 uppercase mb-4">Tambah Device Baru</h2>
                <form action="{{ route('master.devices.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="name" class="block text-xs font-mono tracking-wider text-slate-655 uppercase mb-2">Nama Device *</label>
                        <input type="text" id="name" name="name" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 text-sm focus:outline-none focus:border-blue-600 transition-all placeholder-slate-400" placeholder="Contoh: Device Sensor A1" required>
                    </div>
                    <button type="submit" class="w-full flex items-center justify-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm active:scale-[0.98]">
                        <i class="bi bi-plus-lg"></i> SIMPAN DEVICE
                    </button>
                </form>
            </div>
        </div>

        <!-- Devices list (Right panel) -->
        <div class="lg:col-span-2">
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <h2 class="text-xs font-mono tracking-widest text-slate-600 uppercase mb-4">Daftar Device Terdaftar</h2>
                
                @if($devices->isEmpty())
                    <div class="text-center py-12 text-slate-400 font-mono text-xs uppercase tracking-wider">
                        BELUM ADA DATA DEVICE TERSEDIA
                    </div>
                @else
                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 text-[10px] font-mono tracking-widest text-slate-500 uppercase bg-slate-50">
                                    <th class="py-3 px-4" style="width: 80px;">ID</th>
                                    <th class="py-3 px-4">NAMA DEVICE & EDIT INSTAN</th>
                                    <th class="py-3 px-4 text-right">AKSI</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @foreach($devices as $device)
                                    <tr class="hover:bg-slate-50 transition-all">
                                        <td class="py-3 px-4 font-mono text-xs text-blue-600 font-bold">#{{ $device->id }}</td>
                                        <td class="py-3 px-4">
                                            <form action="{{ route('master.devices.update', $device) }}" method="POST" class="flex gap-2 items-center">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="name" value="{{ $device->name }}" class="bg-white border border-slate-200 focus:border-blue-600 rounded px-2.5 py-1 text-slate-800 text-xs focus:outline-none transition-all w-full max-w-sm font-semibold" required>
                                                <button type="submit" class="p-1 border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-350 text-green-700 rounded text-xs transition-all" title="Simpan Perubahan">
                                                    <i class="bi bi-save-fill"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <form action="{{ route('master.devices.destroy', $device) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus device ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-50 hover:bg-red-100 border border-red-200 text-red-700 rounded text-[11px] font-mono font-bold transition-all shadow-sm">
                                                    <i class="bi bi-trash3-fill"></i> HAPUS
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 border-t border-slate-200 pt-4">
                        {{ $devices->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
