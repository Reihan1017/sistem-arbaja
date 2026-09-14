@extends('layouts.app')

@php $isEdit = $invoice->exists; @endphp

@section('title', $isEdit ? 'Edit Invoice' : 'Buat Invoice')
@section('subtitle', 'Isi rincian barang, biaya, dan tanda tangan kasir')

@section('content')

<form method="POST" action="{{ $isEdit ? route('invoices.update', $invoice) : route('invoices.store') }}" id="invoice-form">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="card mb-3">
        <div class="card-body row g-3">
            <div class="col-md-4">
                <label class="form-label">Tanggal Invoice</label>
                <input type="date" name="doc_date" class="form-control"
                       value="{{ old('doc_date', $invoice->doc_date?->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status Pembayaran</label>
                <select name="payment_status" class="form-select" required>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(old('payment_status', $invoice->payment_status) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Nama Kasir (Penandatangan)</label>
                <input type="text" name="signed_by" class="form-control"
                       value="{{ old('signed_by', $invoice->signed_by) }}">
            </div>
            <div class="col-md-8">
                <label class="form-label">Nama Pelanggan</label>
                <input type="text" name="customer_name" class="form-control"
                       value="{{ old('customer_name', $invoice->customer_name) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">No. Telepon</label>
                <input type="text" name="customer_phone" class="form-control"
                       value="{{ old('customer_phone', $invoice->customer_phone) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">NPWP Pelanggan</label>
                <input type="text" name="customer_npwp" class="form-control"
                       value="{{ old('customer_npwp', $invoice->customer_npwp) }}" placeholder="-">
            </div>
            <div class="col-md-4">
                <label class="form-label">Att / PIC</label>
                <input type="text" name="customer_att" class="form-control"
                       value="{{ old('customer_att', $invoice->customer_att) }}" placeholder="Bag. Finance">
            </div>
            <div class="col-md-12">
                <label class="form-label">Alamat Pengiriman</label>
                <textarea name="customer_address" class="form-control" rows="2">{{ old('customer_address', $invoice->customer_address) }}</textarea>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label">Termin Pembayaran</label>
                <input type="text" name="termin" class="form-control"
                       value="{{ old('termin', $invoice->termin) }}" placeholder="Contoh: DP 20% 80% CBD">
            </div>
        </div>
    </div>

    {{-- Tabel barang dinamis --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label fw-semibold mb-0">Rincian Barang</label>
                <button type="button" id="add-row" class="btn btn-sm btn-success">
                    <i class="bi bi-plus-lg"></i> Tambah Baris
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
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
                        <tr><td>Subtotal</td><td class="text-end" id="txt-subtotal">Rp 0</td></tr>
                        <tr>
                            <td style="width:50%">
                                Diskon (Rp)
                                <input type="number" step="0.01" min="0" name="discount" id="discount"
                                       class="form-control form-control-sm d-inline-block w-auto ms-2"
                                       value="{{ old('discount', $invoice->discount ?? 0) }}">
                            </td>
                            <td class="text-end" id="txt-discount">Rp 0</td>
                        </tr>
                        <tr>
                            <td style="width:50%">
                                PPN (%)
                                <input type="number" step="0.01" min="0" max="100" name="ppn_percent" id="ppn_percent"
                                       class="form-control form-control-sm d-inline-block w-auto ms-2"
                                       value="{{ old('ppn_percent', $invoice->ppn_percent ?? 11) }}">
                            </td>
                            <td class="text-end" id="txt-ppn">Rp 0</td>
                        </tr>
                        <tr>
                            <td style="width:50%">
                                Ongkos Kirim
                                <input type="number" step="0.01" min="0" name="shipping_cost" id="shipping_cost"
                                       class="form-control form-control-sm d-inline-block w-auto ms-2"
                                       value="{{ old('shipping_cost', $invoice->shipping_cost ?? 0) }}">
                            </td>
                            <td class="text-end" id="txt-shipping">Rp 0</td>
                        </tr>
                        <tr class="fw-bold fs-5 border-top">
                            <td>Grand Total</td>
                            <td class="text-end" id="txt-grand">Rp 0</td>
                        </tr>
                        <tr>
                            <td style="width:50%">
                                DP / Uang Muka (Rp)
                                <input type="number" step="0.01" min="0" name="dp_amount" id="dp_amount"
                                       class="form-control form-control-sm d-inline-block w-auto ms-2"
                                       value="{{ old('dp_amount', $invoice->dp_amount ?? 0) }}">
                            </td>
                            <td class="text-end" id="txt-dp">Rp 0</td>
                        </tr>
                        <tr class="fw-bold text-danger border-top">
                            <td>Sisa Pembayaran</td>
                            <td class="text-end" id="txt-remaining">Rp 0</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body row g-3">
            <div class="col-md-12">
                <label class="form-label">Instruksi Pembayaran</label>
                <textarea name="payment_instruction" class="form-control" rows="2"
                          placeholder="Contoh: Transfer ke BCA 123-456-7890 a.n. TB. AR Baja Steelindo">{{ old('payment_instruction', $invoice->payment_instruction) }}</textarea>
            </div>
            <div class="col-md-12">
                <label class="form-label">Catatan</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $invoice->notes) }}</textarea>
            </div>
        </div>
    </div>

    {{-- Signature pad --}}
    <div class="card mb-3">
        <div class="card-body">
            <label class="form-label fw-semibold">Tanda Tangan Kasir</label>
            <div style="border:2px dashed #999; border-radius:6px; width:100%; max-width:500px;">
                <canvas id="signature-pad" width="500" height="180" style="width:100%; touch-action:none;"></canvas>
            </div>
            <div class="mt-2">
                <button type="button" id="clear-signature" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-eraser"></i> Hapus Tanda Tangan
                </button>
            </div>
            <input type="hidden" name="signature" id="signature-input" value="{{ old('signature', $invoice->signature) }}">
        </div>
    </div>

    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save"></i> Simpan Invoice</button>
    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">Batal</a>
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
// ===== Signature Pad =====
const canvas = document.getElementById('signature-pad');
const signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)' });
const signatureInput = document.getElementById('signature-input');

function resizeCanvas() {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext('2d').scale(ratio, ratio);
    signaturePad.clear();
}
window.addEventListener('resize', resizeCanvas);
resizeCanvas();

// Load tanda tangan lama saat edit
const existingSignature = @json($invoice->signature);
if (existingSignature) {
    signaturePad.fromDataURL(existingSignature);
}

document.getElementById('clear-signature').addEventListener('click', () => signaturePad.clear());

document.getElementById('invoice-form').addEventListener('submit', () => {
    signatureInput.value = signaturePad.isEmpty() ? '' : signaturePad.toDataURL('image/png');
});

// ===== Tabel barang dinamis + kalkulator real-time =====
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

    const discount = parseFloat(document.getElementById('discount').value) || 0;
    const netTotal = subtotal - discount;
    const ppnPercent = parseFloat(document.getElementById('ppn_percent').value) || 0;
    const shipping = parseFloat(document.getElementById('shipping_cost').value) || 0;
    const ppnAmount = netTotal * (ppnPercent / 100);
    const grand = netTotal + ppnAmount + shipping;

    document.getElementById('txt-subtotal').textContent = rupiah(subtotal);
    document.getElementById('txt-discount').textContent = '- ' + rupiah(discount);
    document.getElementById('txt-ppn').textContent = rupiah(ppnAmount);
    document.getElementById('txt-shipping').textContent = rupiah(shipping);
    document.getElementById('txt-grand').textContent = rupiah(grand);

    const dp = parseFloat(document.getElementById('dp_amount').value) || 0;
    const remaining = grand - dp;
    document.getElementById('txt-dp').textContent = rupiah(dp);
    document.getElementById('txt-remaining').textContent = rupiah(remaining);
}

document.getElementById('add-row').addEventListener('click', () => addRow());
document.getElementById('discount').addEventListener('input', calculate);
document.getElementById('dp_amount').addEventListener('input', calculate);
document.getElementById('ppn_percent').addEventListener('input', calculate);
document.getElementById('shipping_cost').addEventListener('input', calculate);

const existingItems = @json($invoice->items ?? []);
if (existingItems.length) {
    existingItems.forEach(item => addRow(item));
} else {
    addRow();
}
</script>
@endpush
