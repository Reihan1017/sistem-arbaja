@extends('layouts.app')
@section('title', 'Surat Menyurat')
@section('subtitle', 'Kelola surat keluar, dukungan, dan penawaran')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('documents.create', ['type' => $activeType]) }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Buat {{ \App\Models\Document::TYPES[$activeType] }}
    </a>
</div>

<ul class="nav nav-tabs mb-3">
    @foreach($types as $key => $label)
        <li class="nav-item">
            <a class="nav-link {{ $activeType === $key ? 'active fw-bold' : '' }}"
               href="{{ route('documents.index', ['type' => $key]) }}">{{ $label }}</a>
        </li>
    @endforeach
</ul>

<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <input type="hidden" name="type" value="{{ $activeType }}">
            <div class="col-md-4">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                       placeholder="Cari nomor / nama pelanggan / perihal...">
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Cari</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No. Dokumen</th>
                        <th>Tanggal</th>
                        <th>Pelanggan / Tujuan</th>
                        <th>Perihal</th>
                        @if($activeType === 'quotation')<th class="text-end">Grand Total</th>@endif
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                        <tr>
                            <td class="fw-semibold">{{ $doc->doc_number }}</td>
                            <td>{{ $doc->doc_date->format('d/m/Y') }}</td>
                            <td>{{ $doc->customer_name }}</td>
                            <td>{{ $doc->subject ?? '-' }}</td>
                            @if($activeType === 'quotation')
                                <td class="text-end">Rp {{ number_format($doc->grand_total, 0, ',', '.') }}</td>
                            @endif
                            <td class="text-center">
                                <a href="{{ route('documents.print', $doc) }}" target="_blank"
                                   class="btn btn-sm btn-outline-secondary" title="Cetak">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <a href="{{ route('documents.edit', $doc) }}"
                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if(auth()->user()->isAdmin())
                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                        data-url="{{ route('documents.destroy', $doc) }}" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $documents->links() }}
    </div>
</div>

<form id="delete-form" method="POST" style="display:none">
    @csrf @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', () => {
        Swal.fire({
            title: 'Hapus dokumen ini?',
            text: 'Data yang sudah dihapus tidak dapat dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545',
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('delete-form');
                form.action = btn.dataset.url;
                form.submit();
            }
        });
    });
});
</script>
@endpush
