@extends('layouts.app')

@php
    $isEdit = $document->exists;
    $typeLabel = \App\Models\Document::TYPES[$type];
    $isLetter = in_array($type, ['surat_keluar', 'surat_dukungan']);
@endphp

@section('title', $isEdit ? 'Edit Dokumen' : 'Buat Dokumen')
@section('subtitle', $typeLabel)

@section('content')

<form method="POST" action="{{ $isEdit ? route('documents.update', $document) : route('documents.store') }}">
    @csrf
    @if($isEdit) @method('PUT') @endif
    <input type="hidden" name="type" value="{{ $type }}">

    <div class="card mb-3">
        <div class="card-body row g-3">
            <div class="col-md-4">
                <label class="form-label">Tanggal</label>
                <input type="date" name="doc_date" class="form-control"
                       value="{{ old('doc_date', $document->doc_date?->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">{{ $isLetter ? 'Ditujukan Kepada' : 'Nama Pelanggan' }}</label>
                <input type="text" name="customer_name" class="form-control"
                       value="{{ old('customer_name', $document->customer_name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Alamat</label>
                <textarea name="customer_address" class="form-control" rows="2">{{ old('customer_address', $document->customer_address) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">No. Telepon</label>
                <input type="text" name="customer_phone" class="form-control"
                       value="{{ old('customer_phone', $document->customer_phone) }}">
            </div>
            <div class="col-md-12">
                <label class="form-label">Perihal</label>
                <input type="text" name="subject" class="form-control"
                       value="{{ old('subject', $document->subject) }}"
                       placeholder="Contoh: Penawaran Harga Besi Beton">
            </div>
        </div>
    </div>

    @if($isLetter)
        <div class="card mb-3">
            <div class="card-body">
                <label class="form-label fw-semibold">Isi Surat</label>
                <textarea name="body" class="form-control" rows="10" required>{{ old('body', $document->body) }}</textarea>
            </div>
        </div>
    @else
        {{-- Quotation: tabel barang dinamis + kalkulator real-time --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label fw-semibold mb-0">Rincian Barang</label>
                    <button type="button" id="add-row" class="btn btn-sm btn-success">
                        <i class="bi bi-plus-lg"></i> Tambah Baris
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="items-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width:35%">Nama Barang</th>
                                <th style="width:15%">Satuan</th>
                                <th style="width:12%">Qty</th>
                                <th style="width:18%">Harga Satuan</th>
                                <th style="width:15%">Total</th>
                                <th style="width:5%"></th>
                            </tr>
                        </thead>
                        <tbody id="items-body"></tbody>
                    </table>
                </div>

                <div class="row justify-content-end">
                    <div class="col-md-5">
                        <table class="table table-sm">
                            <tr>
                                <td>Subtotal</td>
                                <td class="text-end" id="txt-subtotal">Rp 0</td>
                            </tr>
                            <tr>
                                <td style="width:50%">
                                    PPN (%)
                                    <input type="number" step="0.01" min="0" max="100" name="ppn_percent" id="ppn_percent"
                                           class="form-control form-control-sm d-inline-block w-auto ms-2"
                                           value="{{ old('ppn_percent', $document->ppn_percent ?? 11) }}">
                                </td>
                                <td class="text-end" id="txt-ppn">Rp 0</td>
                            </tr>
                            <tr>
                                <td style="width:50%">
                                    Ongkos Kirim
                                    <input type="number" step="0.01" min="0" name="shipping_cost" id="shipping_cost"
                                           class="form-control form-control-sm d-inline-block w-auto ms-2"
                                           value="{{ old('shipping_cost', $document->shipping_cost ?? 0) }}">
                                </td>
                                <td class="text-end" id="txt-shipping">Rp 0</td>
                            </tr>
                            <tr class="fw-bold fs-5 border-top">
                                <td>Grand Total</td>
                                <td class="text-end" id="txt-grand">Rp 0</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <label class="form-label">Catatan Tambahan</label>
            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $document->notes) }}</textarea>
        </div>
    </div>

    <button type="submit" class="btn btn-primary px-4">
        <i class="bi bi-save"></i> Simpan {{ $typeLabel }}
    </button>
    <a href="{{ route('documents.index', ['type' => $type]) }}" class="btn btn-outline-secondary">Batal</a>
</form>
@endsection

@if(!$isLetter)
@push('scripts')
<script>
const rupiah = n => 'Rp ' + Number(n || 0).toLocaleString('id-ID', {maximumFractionDigits: 0});
let rowIndex = 0;
const tbody = document.getElementById('items-body');

function addRow(data = {}) {
    const i = rowIndex++;
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" name="items[${i}][name]" class="form-control form-control-sm" value="${data.name ?? ''}" required></td>
        <td><input type="text" name="items[${i}][unit]" class="form-control form-control-sm" value="${data.unit ?? ''}"></td>
        <td><input type="number" step="0.01" min="0.01" name="items[${i}][qty]" class="form-control form-control-sm qty" value="${data.qty ?? 1}" required></td>
        <td><input type="number" step="0.01" min="0" name="items[${i}][price]" class="form-control form-control-sm price" value="${data.price ?? 0}" required></td>
        <td class="text-end row-total">Rp 0</td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove"><i class="bi bi-x-lg"></i></button></td>
    `;
    tbody.appendChild(tr);
    bindRow(tr);
    calculate();
}

function bindRow(tr) {
    tr.querySelectorAll('.qty, .price').forEach(inp => inp.addEventListener('input', calculate));
    tr.querySelector('.btn-remove').addEventListener('click', () => { tr.remove(); calculate(); });
}

function calculate() {
    let subtotal = 0;
    tbody.querySelectorAll('tr').forEach(tr => {
        const qty = parseFloat(tr.querySelector('.qty').value) || 0;
        const price = parseFloat(tr.querySelector('.price').value) || 0;
        const total = qty * price;
        tr.querySelector('.row-total').textContent = rupiah(total);
        subtotal += total;
    });

    const ppnPercent = parseFloat(document.getElementById('ppn_percent').value) || 0;
    const shipping = parseFloat(document.getElementById('shipping_cost').value) || 0;
    const ppnAmount = subtotal * (ppnPercent / 100);
    const grand = subtotal + ppnAmount + shipping;

    document.getElementById('txt-subtotal').textContent = rupiah(subtotal);
    document.getElementById('txt-ppn').textContent = rupiah(ppnAmount);
    document.getElementById('txt-shipping').textContent = rupiah(shipping);
    document.getElementById('txt-grand').textContent = rupiah(grand);
}

document.getElementById('add-row').addEventListener('click', () => addRow());
document.getElementById('ppn_percent').addEventListener('input', calculate);
document.getElementById('shipping_cost').addEventListener('input', calculate);

// Load existing items kalau mode edit
const existingItems = @json($document->items ?? []);
if (existingItems.length) {
    existingItems.forEach(item => addRow(item));
} else {
    addRow();
}
</script>
@endpush
@endif
