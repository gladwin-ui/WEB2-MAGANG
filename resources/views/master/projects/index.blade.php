@extends('layouts.app')

@section('title', 'Kelola Master Project')

@section('content')
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 800;">Kelola Master Project</h1>
    <p style="color: var(--text-secondary);">Tambahkan atau perbarui data master project di lingkungan produksi</p>
</div>

<div class="content-grid" style="grid-template-columns: 1fr 2fr;">
    <!-- Left Column: Add Project Form -->
    <div>
        <div class="card">
            <h2 class="card-title">Tambah Project Baru</h2>
            <form action="{{ route('master.projects.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name" class="form-label">Nama Project *</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Contoh: Project TACA OPSHYB" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i class="bi bi-plus-lg"></i> Simpan Project
                </button>
            </form>
        </div>
    </div>

    <!-- Right Column: Projects List -->
    <div>
        <div class="card">
            <h2 class="card-title">Daftar Project</h2>
            <div class="table-container">
                @if($projects->isEmpty())
                    <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                        Belum ada project terdaftar.
                    </div>
                @else
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th>Nama Project</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $project)
                                <tr>
                                    <td>#{{ $project->id }}</td>
                                    <td>
                                        <!-- Inline edit form -->
                                        <form action="{{ route('master.projects.update', $project) }}" method="POST" id="edit-form-{{ $project->id }}" style="display: flex; gap: 0.5rem; align-items: center;">
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="name" value="{{ $project->name }}" class="form-control" style="padding: 0.35rem 0.5rem; font-size: 0.9rem;" required>
                                            <button type="submit" class="btn btn-secondary btn-sm" title="Simpan Perubahan" style="padding: 0.4rem 0.6rem;">
                                                <i class="bi bi-save-fill" style="color: var(--color-minor);"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <!-- Delete action -->
                                        <form action="{{ route('master.projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus project ini? Semua bug terkait project ini akan kehilangan referensi projectnya.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" style="padding: 0.4rem 0.6rem;">
                                                <i class="bi bi-trash3-fill"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    <div style="margin-top: 1.5rem;">
                        {{ $projects->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
