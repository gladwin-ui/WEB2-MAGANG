@extends('layouts.app')

@section('title', 'Kelola Master Serial Number')

@section('content')
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 800;">Kelola Master Serial Number</h1>
    <p style="color: var(--text-secondary);">Tambahkan atau perbarui data master serial number unit dan sub-komponen</p>
</div>

<div class="content-grid" style="grid-template-columns: 1fr 2fr;">
    <!-- Left Column: Add SN Form -->
    <div>
        <div class="card">
            <h2 class="card-title">Tambah SN Baru</h2>
            <form action="{{ route('master.serial_numbers.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="sn_code" class="form-label">Serial Number / Code *</label>
                    <input type="text" id="sn_code" name="sn_code" class="form-control" placeholder="Contoh: SN_UNIT_TACA_2026-001" required>
                </div>

                <div class="form-group">
                    <label for="project_id" class="form-label">Project Relasi</label>
                    <select id="project_id" name="project_id">
                        <option value="">-- Tanpa Project --</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="type" class="form-label">Tipe Modul *</label>
                    <select id="type" name="type" required>
                        <option value="unit">Unit Utama (Produk)</option>
                        <option value="sub">Sub-komponen (Modul/Part)</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i class="bi bi-plus-lg"></i> Simpan Serial Number
                </button>
            </form>
        </div>
    </div>

    <!-- Right Column: SN List -->
    <div>
        <div class="card">
            <h2 class="card-title">Daftar Serial Number</h2>
            <div class="table-container">
                @if($serialNumbers->isEmpty())
                    <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                        Belum ada serial number terdaftar.
                    </div>
                @else
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th>Serial Code & Edit</th>
                                <th>Project</th>
                                <th>Tipe</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($serialNumbers as $sn)
                                <tr>
                                    <td>#{{ $sn->id }}</td>
                                    <td>
                                        <form action="{{ route('master.serial_numbers.update', $sn) }}" method="POST" style="display: flex; flex-direction: column; gap: 0.5rem; background-color: rgba(255,255,255,0.01); padding: 0.5rem; border-radius: 0.25rem;">
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="sn_code" value="{{ $sn->sn_code }}" class="form-control" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;" required>
                                            
                                            <div style="display: flex; gap: 0.25rem; align-items: center;">
                                                <select name="project_id" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; width: auto; flex-grow: 1;">
                                                    <option value="">-- No Project --</option>
                                                    @foreach($projects as $p)
                                                        <option value="{{ $p->id }}" {{ $sn->project_id == $p->id ? 'selected' : '' }}>#{{ $p->id }}</option>
                                                    @endforeach
                                                </select>
                                                
                                                <select name="type" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; width: auto;">
                                                    <option value="unit" {{ $sn->type === 'unit' ? 'selected' : '' }}>Unit</option>
                                                    <option value="sub" {{ $sn->type === 'sub' ? 'selected' : '' }}>Sub</option>
                                                </select>
                                                
                                                <button type="submit" class="btn btn-secondary btn-sm" title="Update SN" style="padding: 0.3rem 0.5rem;">
                                                    <i class="bi bi-save-fill" style="color: var(--color-minor); font-size: 0.75rem;"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                    <td>
                                        {{ $sn->project?->name ?? 'None' }}
                                    </td>
                                    <td>
                                        @if($sn->type === 'unit')
                                            <span class="badge badge-minor">Unit</span>
                                        @else
                                            <span class="badge badge-spam">Sub</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('master.serial_numbers.destroy', $sn) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus serial number ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" style="padding: 0.35rem 0.5rem; font-size: 0.85rem;">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    <div style="margin-top: 1.5rem;">
                        {{ $serialNumbers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
