@extends('layouts.app')
@section('title', 'Invoice & Pengiriman')
@section('subtitle', 'Penagihan, status pembayaran, dan cetak Delivery Order')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Buat Invoice
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                       placeholder="Cari no. invoice / nama pelanggan...">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Cari</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No. Invoice</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th class="text-end">Grand Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td class="fw-semibold">{{ $inv->doc_number }}</td>
                            <td>{{ $inv->doc_date->format('d/m/Y') }}</td>
                            <td>{{ $inv->customer_name }}</td>
                            <td class="text-end">Rp {{ number_format($inv->grand_total, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $inv->statusBadgeColor() }} btn-status"
                                      role="button" style="cursor:pointer"
                                      data-id="{{ $inv->id }}"
                                      data-url="{{ route('invoices.updateStatus', $inv) }}"
                                      data-current="{{ $inv->payment_status }}">
                                    {{ $inv->statusLabel() }} <i class="bi bi-pencil-fill" style="font-size:9px"></i>
                                </span>
                            </td>
                            <td class="text-center text-nowrap">
                                <a href="{{ route('invoices.print.invoice', $inv) }}" target="_blank"
                                   class="btn btn-sm btn-outline-success" title="Cetak Invoice">
                                    <i class="bi bi-receipt"></i> Inv
                                </a>
                                <a href="{{ route('invoices.print.do', $inv) }}" target="_blank"
                                   class="btn btn-sm btn-outline-info" title="Cetak Delivery Order">
                                    <i class="bi bi-truck"></i> DO
                                </a>
                                <a href="{{ route('invoices.edit', $inv) }}"
                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if(auth()->user()->isAdmin())
                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                        data-url="{{ route('invoices.destroy', $inv) }}" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada invoice.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $invoices->links() }}
    </div>
</div>

<form id="delete-form" method="POST" style="display:none">
    @csrf @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
const STATUS_OPTIONS = @json($statuses); // {DRAFT: 'Draft', LUNAS: 'Lunas', ...}
const STATUS_COLORS = { DRAFT: 'secondary', LUNAS: 'success', COD: 'primary', TEMPO: 'warning', BATAL: 'danger' };

document.querySelectorAll('.btn-status').forEach(el => {
    el.addEventListener('click', () => {
        const options = Object.fromEntries(Object.entries(STATUS_OPTIONS));

        Swal.fire({
            title: 'Ubah Status Pembayaran',
            input: 'select',
            inputOptions: options,
            inputValue: el.dataset.current,
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
        }).then(result => {
            if (!result.isConfirmed) return;

            fetch(el.dataset.url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.CSRF_TOKEN,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ payment_status: result.value }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    el.className = 'badge bg-' + data.color + ' btn-status';
                    el.innerHTML = data.label + ' <i class="bi bi-pencil-fill" style="font-size:9px"></i>';
                    el.dataset.current = data.status;
                    Swal.fire({ icon: 'success', title: 'Status diperbarui', timer: 1200, showConfirmButton: false });
                }
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'Gagal memperbarui status' }));
        });
    });
});

document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', () => {
        Swal.fire({
            title: 'Hapus invoice ini?',
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
