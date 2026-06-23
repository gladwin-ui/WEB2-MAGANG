@extends('layouts.app')

@section('title', 'Kelola Master Serial Number')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-black text-slate-100 tracking-tight uppercase">KELOLA MASTER SERIAL NUMBER</h1>
        <p class="text-xs text-slate-400 font-mono tracking-wider uppercase">DAFTAR KODE UNIT & PART BATCH CADANGAN PRODUKSI PT HARIFF</p>
    </div>

    <!-- Layout Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Add SN (Left Panel) -->
        <div>
            <div class="bg-slate-900/40 border border-slate-800 rounded-xl p-6 shadow-xl backdrop-blur-sm">
                <h2 class="text-xs font-mono tracking-widest text-slate-500 uppercase mb-4">Tambah SN Baru</h2>
                <form action="{{ route('master.serial_numbers.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="sn_code" class="block text-xs font-mono tracking-wider text-slate-450 uppercase mb-2">Kode Serial Number *</label>
                        <input type="text" id="sn_code" name="sn_code" class="w-full bg-slate-950 border border-slate-850 rounded-lg px-4 py-2 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition-all placeholder-slate-800 font-mono" placeholder="SN_UNIT_TACA_2026-001" required>
                    </div>

                    <div>
                        <label for="project_id" class="block text-xs font-mono tracking-wider text-slate-450 uppercase mb-2">Relasi Project</label>
                        <select id="project_id" name="project_id" class="w-full bg-slate-950 border border-slate-850 rounded-lg px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition-all">
                            <option value="">-- Tanpa Project --</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="type" class="block text-xs font-mono tracking-wider text-slate-450 uppercase mb-2">Tipe Komponen / Modul *</label>
                        <select id="type" name="type" class="w-full bg-slate-950 border border-slate-850 rounded-lg px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition-all" required>
                            <option value="unit">Unit Utama (Produk)</option>
                            <option value="sub">Sub-komponen (Part/PCB)</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full flex items-center justify-center gap-1.5 px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-lg text-xs font-bold transition-all shadow-md transform active:scale-[0.98] pt-2">
                        <i class="bi bi-plus-lg"></i> SIMPAN SERIAL NUMBER
                    </button>
                </form>
            </div>
        </div>

        <!-- SN List (Right Panel) -->
        <div class="lg:col-span-2">
            <div class="bg-slate-900/40 border border-slate-800 rounded-xl p-6 shadow-xl backdrop-blur-sm">
                <h2 class="text-xs font-mono tracking-widest text-slate-500 uppercase mb-4">Daftar Serial Number Terdaftar</h2>
                
                @if($serialNumbers->isEmpty())
                    <div class="text-center py-12 text-slate-550 font-mono text-xs uppercase tracking-wider">
                        BELUM ADA DATA SERIAL NUMBER TERSEDIA
                    </div>
                @else
                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-800 text-[10px] font-mono tracking-widest text-slate-500 uppercase">
                                    <th class="py-3 px-4" style="width: 60px;">ID</th>
                                    <th class="py-3 px-4">SERIAL CODE & EDIT</th>
                                    <th class="py-3 px-4">RELASI PROYEK</th>
                                    <th class="py-3 px-4">TIPE</th>
                                    <th class="py-3 px-4 text-right">AKSI</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/50 text-sm">
                                @foreach($serialNumbers as $sn)
                                    <tr class="hover:bg-slate-900/20 transition-all">
                                        <td class="py-3 px-4 font-mono text-xs text-indigo-400 font-bold">#{{ $sn->id }}</td>
                                        <td class="py-3 px-4">
                                            <!-- Inline edit configuration -->
                                            <form action="{{ route('master.serial_numbers.update', $sn) }}" method="POST" class="space-y-1.5 p-2 bg-slate-950/40 border border-slate-850/60 rounded">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="sn_code" value="{{ $sn->sn_code }}" class="bg-slate-950 border border-slate-850 focus:border-indigo-500 rounded px-2 py-0.5 text-slate-200 text-xs font-mono font-bold focus:outline-none transition-all w-full" required>
                                                
                                                <div class="flex gap-1.5 items-center">
                                                    <select name="project_id" class="bg-slate-950 border border-slate-850 text-slate-350 text-[10px] px-1 py-0.5 rounded focus:outline-none">
                                                        <option value="">-- No Proj --</option>
                                                        @foreach($projects as $p)
                                                            <option value="{{ $p->id }}" {{ $sn->project_id == $p->id ? 'selected' : '' }}>#{{ $p->id }}</option>
                                                        @endforeach
                                                    </select>
                                                    
                                                    <select name="type" class="bg-slate-950 border border-slate-850 text-slate-350 text-[10px] px-1 py-0.5 rounded focus:outline-none">
                                                        <option value="unit" {{ $sn->type === 'unit' ? 'selected' : '' }}>Unit</option>
                                                        <option value="sub" {{ $sn->type === 'sub' ? 'selected' : '' }}>Sub</option>
                                                    </select>
                                                    
                                                    <button type="submit" class="p-0.5 border border-slate-800 hover:bg-slate-800 text-emerald-450 hover:text-emerald-350 rounded text-xs transition-all ml-auto" title="Update SN">
                                                        <i class="bi bi-save-fill" style="font-size: 0.7rem;"></i>
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                        <td class="py-3 px-4 font-semibold text-slate-400">
                                            {{ $sn->project?->name ?? 'None' }}
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($sn->type === 'unit')
                                                <span class="inline-flex text-[8px] font-extrabold font-mono bg-blue-500/10 text-blue-400 border border-blue-500/20 px-1.5 py-0.2 rounded uppercase">UNIT</span>
                                            @else
                                                <span class="inline-flex text-[8px] font-extrabold font-mono bg-violet-500/10 text-violet-400 border border-violet-500/20 px-1.5 py-0.2 rounded uppercase">SUB</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <form action="{{ route('master.serial_numbers.destroy', $sn) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus serial number ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 text-red-400 hover:text-red-300 rounded text-xs transition-all">
                                                    <i class="bi-trash3-fill"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 border-t border-slate-800 pt-4">
                        {{ $serialNumbers->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
