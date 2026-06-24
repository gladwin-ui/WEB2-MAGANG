@extends('layouts.app')

@section('title', 'Kelola Master Device')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-black text-slate-100 tracking-tight uppercase">KELOLA MASTER DEVICE</h1>
        <p class="text-xs text-slate-400 font-mono tracking-wider uppercase">TAMBAHKAN ATAU PERBARUI REFERENSI DATA DEVICE PT HARIFF</p>
    </div>

    <!-- Layout Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Add Device (Left Panel) -->
        <div>
            <div class="bg-slate-900/40 border border-slate-800 rounded-xl p-6 shadow-xl backdrop-blur-sm">
                <h2 class="text-xs font-mono tracking-widest text-slate-500 uppercase mb-4">Tambah Device Baru</h2>
                <form action="{{ route('master.devices.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="name" class="block text-xs font-mono tracking-wider text-slate-450 uppercase mb-2">Nama Device *</label>
                        <input type="text" id="name" name="name" class="w-full bg-slate-950 border border-slate-850 rounded-lg px-4 py-2 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition-all placeholder-slate-800" placeholder="Contoh: Device Sensor A1" required>
                    </div>
                    <button type="submit" class="w-full flex items-center justify-center gap-1.5 px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-lg text-xs font-bold transition-all shadow-md transform active:scale-[0.98]">
                        <i class="bi bi-plus-lg"></i> SIMPAN DEVICE
                    </button>
                </form>
            </div>
        </div>

        <!-- Devices list (Right panel) -->
        <div class="lg:col-span-2">
            <div class="bg-slate-900/40 border border-slate-800 rounded-xl p-6 shadow-xl backdrop-blur-sm">
                <h2 class="text-xs font-mono tracking-widest text-slate-500 uppercase mb-4">Daftar Device Terdaftar</h2>
                
                @if($devices->isEmpty())
                    <div class="text-center py-12 text-slate-550 font-mono text-xs uppercase tracking-wider">
                        BELUM ADA DATA DEVICE TERSEDIA
                    </div>
                @else
                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-800 text-[10px] font-mono tracking-widest text-slate-500 uppercase">
                                    <th class="py-3 px-4" style="width: 80px;">ID</th>
                                    <th class="py-3 px-4">NAMA DEVICE & EDIT INSTAN</th>
                                    <th class="py-3 px-4 text-right">AKSI</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/50 text-sm">
                                @foreach($devices as $device)
                                    <tr class="hover:bg-slate-900/20 transition-all">
                                        <td class="py-3 px-4 font-mono text-xs text-indigo-400 font-bold">#{{ $device->id }}</td>
                                        <td class="py-3 px-4">
                                            <form action="{{ route('master.devices.update', $device) }}" method="POST" class="flex gap-2 items-center">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="name" value="{{ $device->name }}" class="bg-slate-950/70 border border-slate-850 focus:border-indigo-500 rounded px-2.5 py-1 text-slate-200 text-xs focus:outline-none transition-all w-full max-w-sm font-semibold" required>
                                                <button type="submit" class="p-1 border border-slate-800 hover:bg-slate-800 hover:border-slate-700 text-emerald-450 hover:text-emerald-300 rounded text-xs transition-all" title="Simpan Perubahan">
                                                    <i class="bi bi-save-fill"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <form action="{{ route('master.devices.destroy', $device) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus device ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 text-red-400 hover:text-red-300 rounded text-[11px] font-mono font-bold transition-all">
                                                    <i class="bi bi-trash3-fill"></i> HAPUS
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 border-t border-slate-800 pt-4">
                        {{ $devices->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
