@extends('layouts.app')

@section('title', 'Kelola Master Serial Number')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 mb-6 border-b border-border-strong">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-bg-secondary border border-border-default flex items-center justify-center shrink-0 shadow-sm">
                <i class="bi bi-upc-scan text-xl text-accent"></i>
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-black text-text-primary tracking-tight">Kelola Master Serial Number</h1>
                <p class="text-xs md:text-sm text-text-secondary mt-0.5 font-medium">Daftar kode unit dan part batch cadangan produksi PT Hariff</p>
            </div>
        </div>
    </div>

    <!-- Layout Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Add SN (Left Panel) -->
        <div>
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <h2 class="text-xs font-mono tracking-widest text-slate-600 uppercase mb-4">Tambah SN Baru</h2>
                <form action="{{ route('master.serial_numbers.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="sn_code" class="block text-xs font-mono tracking-wider text-slate-650 uppercase mb-2">Kode Serial Number *</label>
                        <input type="text" id="sn_code" name="sn_code" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-slate-800 text-sm focus:outline-none focus:border-blue-600 transition-all placeholder-slate-400 font-mono" placeholder="SN_UNIT_TACA_2026-001" required>
                    </div>

                    <div>
                        <label for="project_id" class="block text-xs font-mono tracking-wider text-slate-655 uppercase mb-2">Relasi Project</label>
                        <select id="project_id" name="project_id" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2.5 text-slate-850 text-sm focus:outline-none focus:border-blue-600 transition-all">
                            <option value="">-- Tanpa Project --</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="type" class="block text-xs font-mono tracking-wider text-slate-655 uppercase mb-2">Tipe Komponen / Modul *</label>
                        <select id="type" name="type" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2.5 text-slate-850 text-sm focus:outline-none focus:border-blue-600 transition-all" required>
                            <option value="unit">Unit Utama (Produk)</option>
                            <option value="sub">Sub-komponen (Part/PCB)</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full flex items-center justify-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm active:scale-[0.98] pt-2">
                        <i class="bi bi-plus-lg"></i> SIMPAN SERIAL NUMBER
                    </button>
                </form>
            </div>
        </div>

        <!-- SN List (Right Panel) -->
        <div class="lg:col-span-2">
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <h2 class="text-xs font-mono tracking-widest text-slate-600 uppercase mb-4">Daftar Serial Number Terdaftar</h2>
                
                @if($serialNumbers->isEmpty())
                    <div class="text-center py-12 text-slate-400 font-mono text-xs uppercase tracking-wider">
                        BELUM ADA DATA SERIAL NUMBER TERSEDIA
                    </div>
                @else
                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 text-[10px] font-mono tracking-widest text-slate-500 uppercase bg-slate-50">
                                    <th class="py-3 px-4" style="width: 60px;">ID</th>
                                    <th class="py-3 px-4">SERIAL CODE & EDIT</th>
                                    <th class="py-3 px-4">RELASI PROYEK</th>
                                    <th class="py-3 px-4">TIPE</th>
                                    <th class="py-3 px-4 text-right">AKSI</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @foreach($serialNumbers as $sn)
                                    <tr class="hover:bg-slate-50 transition-all">
                                        <td class="py-3 px-4 font-mono text-xs text-blue-600 font-bold">#{{ $sn->id }}</td>
                                        <td class="py-3 px-4">
                                            <!-- Inline edit configuration -->
                                            <form action="{{ route('master.serial_numbers.update', $sn) }}" method="POST" class="space-y-1.5 p-2 bg-slate-50 border border-slate-200 rounded">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="sn_code" value="{{ $sn->sn_code }}" class="bg-white border border-slate-200 focus:border-blue-600 rounded px-2 py-0.5 text-slate-800 text-xs font-mono font-bold focus:outline-none transition-all w-full" required>
                                                
                                                <div class="flex gap-1.5 items-center">
                                                    <select name="project_id" class="bg-white border border-slate-200 text-slate-700 text-[10px] px-1 py-0.5 rounded focus:outline-none">
                                                        <option value="">-- No Proj --</option>
                                                        @foreach($projects as $p)
                                                            <option value="{{ $p->id }}" {{ $sn->project_id == $p->id ? 'selected' : '' }}>#{{ $p->id }}</option>
                                                        @endforeach
                                                    </select>
                                                    
                                                    <select name="type" class="bg-white border border-slate-200 text-slate-700 text-[10px] px-1 py-0.5 rounded focus:outline-none">
                                                        <option value="unit" {{ $sn->type === 'unit' ? 'selected' : '' }}>Unit</option>
                                                        <option value="sub" {{ $sn->type === 'sub' ? 'selected' : '' }}>Sub</option>
                                                    </select>
                                                    
                                                    <button type="submit" class="p-0.5 border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-green-700 rounded text-xs transition-all ml-auto" title="Update SN">
                                                        <i class="bi bi-save-fill" style="font-size: 0.7rem;"></i>
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                        <td class="py-3 px-4 font-semibold text-slate-650">
                                            {{ $sn->project?->name ?? 'None' }}
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($sn->type === 'unit')
                                                <span class="inline-flex text-[8px] font-extrabold font-mono bg-blue-50 text-blue-700 border border-blue-200 px-1.5 py-0.2 rounded uppercase">UNIT</span>
                                            @else
                                                <span class="inline-flex text-[8px] font-extrabold font-mono bg-purple-100 text-purple-700 border border-purple-200 px-1.5 py-0.2 rounded uppercase">SUB</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <form action="{{ route('master.serial_numbers.destroy', $sn) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus serial number ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 bg-red-50 hover:bg-red-100 border border-red-200 text-red-700 rounded text-xs transition-all shadow-sm">
                                                    <i class="bi-trash3-fill"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 border-t border-slate-200 pt-4">
                        {{ $serialNumbers->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
