@extends('layouts.app')

@section('title', 'Keranjang Sampah (Trash)')

@section('content')
<div class="max-w-5xl mx-auto">

    {{-- Page Header --}}
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('import.upload') }}" class="text-xs text-blue-600 hover:underline flex items-center gap-1 font-mono uppercase">
                    <i class="bi bi-arrow-left"></i> Kembali ke Import
                </a>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight mb-1">Keranjang Sampah (Trash)</h1>
            <p class="text-sm text-slate-500">
                Daftar file SQL / batch reset yang di-soft-delete dari dashboard aktif. Anda dapat memulihkannya atau menghapusnya selamanya.
            </p>
        </div>
        @if($jobs->count() > 0)
            <div class="shrink-0 flex flex-wrap items-center gap-2">
                <form action="{{ route('import.restore_all') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memulihkan semua data bug dari sampah?')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                        <i class="bi bi-arrow-counterclockwise"></i> Pulihkan Semua Data
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="mb-5 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm flex items-start gap-3">
            <i class="bi bi-check-circle-fill text-base shrink-0 mt-0.5 text-green-600"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if($errors->any())
        <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-start gap-3">
            <i class="bi bi-exclamation-triangle-fill text-base shrink-0 mt-0.5"></i>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Table Card --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-card overflow-hidden">
        @if($jobs->count() === 0)
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center mb-4">
                    <i class="bi bi-trash2 text-2xl text-slate-400"></i>
                </div>
                <p class="text-slate-500 font-semibold text-sm mb-1">Keranjang sampah kosong</p>
                <p class="text-slate-400 text-xs">Tidak ada file SQL atau batch reset yang di-soft-delete.</p>
            </div>
        @else
            <form id="trash-table-form" action="{{ route('import.force_delete_selected') }}" method="POST" onsubmit="return confirmDeleteSelected(event)">
                @csrf
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50 text-xs text-slate-500 uppercase tracking-wide font-semibold">
                                <th class="px-4 py-3.5 text-left">
                                    <input type="checkbox" id="select-all" class="form-checkbox h-4 w-4 text-blue-600 border-slate-300 rounded">
                                </th>
                                <th class="px-6 py-3.5 text-left">Nama File / Batch Reset</th>
                                <th class="px-4 py-3.5 text-right">Jumlah Baris</th>
                                <th class="px-4 py-3.5 text-left">Dihapus Pada</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach($jobs as $job)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-4 py-4 text-center">
                                        <input type="checkbox" name="selected_jobs[]" value="{{ $job->id }}" class="row-checkbox form-checkbox h-4 w-4 text-blue-600 border-slate-300 rounded">
                                    </td>
                                    {{-- File / Batch Name --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            @if(str_contains($job->filename, 'Reset'))
                                                <i class="bi bi-arrow-counterclockwise text-red-500 text-lg"></i>
                                            @else
                                                <i class="bi bi-file-earmark-code text-blue-500 text-lg"></i>
                                            @endif
                                            <div>
                                                <div class="font-bold text-slate-800 text-sm">{{ $job->filename }}</div>
                                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">Job ID: #{{ $job->id }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Rows Count --}}
                                    <td class="px-4 py-4 text-right font-mono text-slate-650 font-semibold text-xs">
                                        {{ number_format($job->total_rows) }} baris
                                    </td>

                                    {{-- Deleted At --}}
                                    <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-500 font-mono">
                                        {{ $job->deleted_at->format('Y-m-d H:i:s') }}
                                        <div class="text-[10px] text-slate-400 mt-0.5">{{ $job->deleted_at->diffForHumans() }}</div>
                                    </td>

                                    {{-- Action --}}
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <div class="inline-flex gap-2">
                                            {{-- Restore --}}
                                            <form action="{{ route('import.restore', $job->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 border border-blue-200 hover:border-blue-600 bg-blue-50 text-blue-600 hover:text-blue-750 rounded-lg text-xs font-semibold transition-all">
                                                    <i class="bi bi-arrow-counterclockwise"></i> Pulihkan
                                                </button>
                                            </form>

                                            {{-- Hard Delete --}}
                                            <form action="{{ route('import.force_delete', $job->id) }}" method="POST" onsubmit="return confirm('PENTING: Anda akan menghapus file/batch ini beserta seluruh data bug-nya SELAMANYA dari database. Tindakan ini TIDAK dapat dipulihkan. Apakah Anda yakin?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 border border-red-200 hover:border-red-600 bg-red-50 text-red-650 hover:text-red-750 rounded-lg text-xs font-semibold transition-all">
                                                    <i class="bi bi-trash-fill"></i> Hapus Selamanya
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-100 flex items-center justify-between gap-4 flex-col sm:flex-row">
                    <button id="delete-selected-button" type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors disabled:opacity-50" disabled>
                        <i class="bi bi-trash-fill"></i> Hapus Terpilih
                    </button>
                    @if($jobs->hasPages())
                        <div>
                            {{ $jobs->links() }}
                        </div>
                    @endif
                </div>
            </form>
        @endif
    </div>

</div>
@endsection

@section('scripts')
<script>
    const selectAll = document.getElementById('select-all');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const deleteSelectedButton = document.getElementById('delete-selected-button');

    function updateDeleteButtonState() {
        const anyChecked = Array.from(rowCheckboxes).some(checkbox => checkbox.checked);
        deleteSelectedButton.disabled = !anyChecked;
    }

    function confirmDeleteSelected(event) {
        if (!Array.from(rowCheckboxes).some(checkbox => checkbox.checked)) {
            alert('Pilih setidaknya satu file atau batch untuk dihapus.');
            event.preventDefault();
            return false;
        }
        return confirm('PENTING: Anda akan menghapus selamanya file/batch terpilih beserta semua data bug di dalamnya. Tindakan ini tidak dapat dibatalkan. Lanjutkan?');
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            rowCheckboxes.forEach(checkbox => checkbox.checked = selectAll.checked);
            updateDeleteButtonState();
        });
    }

    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            if (!this.checked) {
                selectAll.checked = false;
            }
            updateDeleteButtonState();
        });
    });

    window.confirmDeleteSelected = confirmDeleteSelected;
    updateDeleteButtonState();
</script>
@endsection
