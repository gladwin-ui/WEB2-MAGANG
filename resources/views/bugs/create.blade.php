@extends('layouts.app')

@section('title', 'Laporkan Bug Baru')

@section('content')
<div class="space-y-6">
    <!-- Back arrow -->
    <div>
        <a href="{{ route('bugs.index') }}" class="text-xs text-slate-400 hover:text-white font-mono tracking-wider uppercase inline-flex items-center gap-1">
            <i class="bi bi-arrow-left"></i> KEMBALI KE QUEUE
        </a>
    </div>

    <!-- Header -->
    <div>
        <h1 class="text-2xl font-black text-slate-100 tracking-tight uppercase">LAPORKAN MASALAH / DEFECT BARU</h1>
        <p class="text-xs text-slate-400 font-mono tracking-wider uppercase">SUBMIT CACAT PRODUKSI KE SISTEM MONITORING INTEGRASI PT HARIFF</p>
    </div>

    <!-- Form Panel -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-xl p-6 shadow-xl backdrop-blur-sm max-w-4xl">
        <form action="{{ route('bugs.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <label for="title" class="block text-xs font-mono tracking-wider text-slate-450 uppercase mb-2">Judul Masalah / Bug Title *</label>
                <input type="text" id="title" name="title" class="w-full bg-slate-950 border border-slate-850 rounded-lg px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition-all placeholder-slate-700" placeholder="Contoh: DCDC short hubung singkat pada unit TACA" value="{{ old('title') }}" required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="project_id" class="block text-xs font-mono tracking-wider text-slate-450 uppercase mb-2">Project Relasi / Kode Proyek</label>
                    <select id="project_id" name="project_id" class="w-full bg-slate-950 border border-slate-850 rounded-lg px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition-all">
                        <option value="">-- Pilih Project --</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }} (ID: {{ $project->id }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="severity" class="block text-xs font-mono tracking-wider text-slate-450 uppercase mb-2">Severity Laporan (Oleh Reporter) *</label>
                    <select id="severity" name="severity" class="w-full bg-slate-950 border border-slate-850 rounded-lg px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition-all" required>
                        <option value="Minor" {{ old('severity') === 'Minor' ? 'selected' : '' }}>Minor (Kosmetik / Cacat Ringan)</option>
                        <option value="Major" {{ old('severity') === 'Major' || !old('severity') ? 'selected' : '' }}>Major (Kerusakan Fungsional)</option>
                        <option value="Critical" {{ old('severity') === 'Critical' ? 'selected' : '' }}>Critical (Ledakan, Api, Hubung Singkat)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="serial_number_id" class="block text-xs font-mono tracking-wider text-slate-450 uppercase mb-2">Serial Number Alat / ID SN</label>
                    <select id="serial_number_id" name="serial_number_id" class="w-full bg-slate-950 border border-slate-850 rounded-lg px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition-all">
                        <option value="">-- Pilih Serial Number --</option>
                        @foreach($serialNumbers as $sn)
                            <option value="{{ $sn->id }}" {{ old('serial_number_id') == $sn->id ? 'selected' : '' }}>
                                {{ $sn->sn_code }} [{{ strtoupper($sn->type) }}] (Project: #{{ $sn->project_id }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="reporter_type" class="block text-xs font-mono tracking-wider text-slate-450 uppercase mb-2">Tipe Laporan / Origin *</label>
                    <select id="reporter_type" name="reporter_type" class="w-full bg-slate-950 border border-slate-850 rounded-lg px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition-all" required>
                        <option value="produk" {{ old('reporter_type') === 'produk' ? 'selected' : '' }}>Produk (Unit Modul Utama)</option>
                        <option value="sub" {{ old('reporter_type') === 'sub' ? 'selected' : '' }}>Sub-komponen (Modul Part PCB)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="product_version" class="block text-xs font-mono tracking-wider text-slate-450 uppercase mb-2">Versi Produk / Software / Hardware</label>
                    <input type="text" id="product_version" name="product_version" class="w-full bg-slate-950 border border-slate-850 rounded-lg px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition-all placeholder-slate-800" placeholder="Contoh: v.20260224" value="{{ old('product_version') }}">
                </div>

                <div>
                    <label for="environment" class="block text-xs font-mono tracking-wider text-slate-450 uppercase mb-2">Kondisi Lingkungan Uji / Environment</label>
                    <input type="text" id="environment" name="environment" class="w-full bg-slate-950 border border-slate-850 rounded-lg px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition-all placeholder-slate-800" placeholder="Contoh: posko lapangan, berdebu, lembap" value="{{ old('environment') }}">
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-mono tracking-wider text-slate-450 uppercase mb-2">Deskripsi Kerusakan / detail cacat *</label>
                <textarea id="description" name="description" rows="4" class="w-full bg-slate-950 border border-slate-850 rounded-lg px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition-all placeholder-slate-800" placeholder="Jelaskan kegagalan komponen, indikator fisik, atau gejala short circuit yang muncul..." required>{{ old('description') }}</textarea>
                <small class="block text-[10px] text-slate-500 font-mono tracking-wide mt-1.5 uppercase">
                    * DESKRIPSI TINGKAT KEPARAHAN AKAN DIPROSES SECARA SINKRON OLEH AI UNTUK DIAGNOSIS SPAM & SEVERITY
                </small>
            </div>

            <div>
                <label for="reproduce_steps" class="block text-xs font-mono tracking-wider text-slate-450 uppercase mb-2">Langkah Reproduksi Masalah</label>
                <textarea id="reproduce_steps" name="reproduce_steps" rows="3" class="w-full bg-slate-950 border border-slate-850 rounded-lg px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition-all placeholder-slate-800" placeholder="1. Nyalakan catu daya 12V&#10;2. Hubungkan pin modul&#10;3. Modul mengalami panas ekstrem dalam 1 menit...">{{ old('reproduce_steps') }}</textarea>
            </div>

            <div>
                <label for="expected_result" class="block text-xs font-mono tracking-wider text-slate-450 uppercase mb-2">Hasil Ekspektasi Fungsional</label>
                <textarea id="expected_result" name="expected_result" rows="2" class="w-full bg-slate-950 border border-slate-850 rounded-lg px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition-all placeholder-slate-800" placeholder="Catu daya stabil dan indikator led menyala normal hijau...">{{ old('expected_result') }}</textarea>
            </div>

            <div>
                <label for="attachment" class="block text-xs font-mono tracking-wider text-slate-450 uppercase mb-2">Lampiran Media / File Log (Foto/Video/Text/Log)</label>
                <input type="file" id="attachment" name="attachment" class="w-full bg-slate-950 border border-slate-850 rounded-lg px-4 py-2 text-slate-450 text-xs focus:outline-none focus:border-indigo-500 transition-all file:mr-4 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-slate-800 file:text-slate-200 hover:file:bg-slate-700 cursor-pointer">
                <span class="block text-[10px] text-slate-500 font-mono tracking-wide mt-1.5 uppercase">UKURAN MAKSIMAL: 20MB. DUKUNGAN FILE: JPEG, PNG, MP4, TXT, LOG, PDF.</span>
            </div>

            <div class="flex gap-3 pt-4 border-t border-slate-800">
                <button type="submit" class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-lg text-sm font-bold shadow-lg shadow-indigo-500/10 transition-all transform active:scale-[0.98]">
                    <i class="bi bi-send-fill"></i> KIRIM LAPORAN (RUN AI ENGINE)
                </button>
                <a href="{{ route('bugs.index') }}" class="flex items-center gap-2 px-5 py-2.5 bg-slate-950 border border-slate-850 hover:bg-slate-900 text-slate-350 hover:text-slate-200 rounded-lg text-sm font-semibold transition-all">
                    BATAL
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
