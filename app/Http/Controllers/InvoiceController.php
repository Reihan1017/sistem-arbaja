<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Document::type(Document::TYPE_INVOICE)
            ->search($request->get('q'))
            ->when($request->filled('status'), fn ($q) => $q->where('payment_status', $request->get('status')))
            ->latest('doc_date')
            ->paginate(15)
            ->withQueryString();

        return view('invoices.index', [
            'invoices' => $invoices,
            'statuses' => Document::STATUSES,
        ]);
    }

    public function create()
    {
        return view('invoices.create', [
            'invoice' => new Document([
                'type'         => Document::TYPE_INVOICE,
                'doc_date'     => now()->toDateString(),
                'ppn_percent'  => 11,
                'payment_status' => Document::STATUS_DRAFT,
            ]),
            'statuses' => Document::STATUSES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        $invoice = new Document($validated);
        $invoice->type = Document::TYPE_INVOICE;
        $invoice->doc_number = Document::generateDocNumber(Document::TYPE_INVOICE);
        $invoice->created_by = Auth::id();
        $invoice->items = $this->normalizeItems($request->input('items', []));
        $invoice->recalculateTotals();
        $invoice->save();

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Invoice berhasil dibuat: ' . $invoice->doc_number);
    }

    public function edit(Document $invoice)
    {
        abort_unless($invoice->type === Document::TYPE_INVOICE, 404);

        return view('invoices.create', [
            'invoice'  => $invoice,
            'statuses' => Document::STATUSES,
        ]);
    }

    public function update(Request $request, Document $invoice)
    {
        abort_unless($invoice->type === Document::TYPE_INVOICE, 404);

        $validated = $this->validateRequest($request);
        $invoice->fill($validated);
        $invoice->items = $this->normalizeItems($request->input('items', []));
        $invoice->recalculateTotals();
        $invoice->save();

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Invoice berhasil diperbarui.');
    }

    public function destroy(Document $invoice)
    {
        abort_unless($invoice->type === Document::TYPE_INVOICE, 404);

        $invoice->delete();

        return back()->with('success', 'Invoice berhasil dihapus.');
    }

    /**
     * Update status pembayaran via AJAX (dipanggil dari dropdown SweetAlert2 di tabel index).
     */
    public function updateStatus(Request $request, Document $invoice)
    {
        abort_unless($invoice->type === Document::TYPE_INVOICE, 404);

        $request->validate([
            'payment_status' => ['required', Rule::in(array_keys(Document::STATUSES))],
        ]);

        $invoice->update(['payment_status' => $request->input('payment_status')]);

        return response()->json([
            'success' => true,
            'status'  => $invoice->payment_status,
            'label'   => $invoice->statusLabel(),
            'color'   => $invoice->statusBadgeColor(),
        ]);
    }

    /**
     * Cetak Invoice (struk tagihan lengkap dengan harga, PPN, ongkir, watermark, stempel, TTD).
     */
    public function printInvoice(Document $invoice)
    {
        abort_unless($invoice->type === Document::TYPE_INVOICE, 404);

        $pdf = Pdf::loadView('invoices.print_invoice', ['invoice' => $invoice])->setPaper('a4');

        return $pdf->stream($this->safeFilename($invoice->doc_number) . '-INVOICE.pdf');
    }

    /**
     * Cetak Delivery Order / Surat Jalan.
     * Menyalin 100% data barang dari invoice yang sama, tapi harga disembunyikan
     * dan bagian bawah diganti kolom TTD Sopir/Gudang/Penerima.
     */
    public function printDeliveryOrder(Document $invoice)
    {
        abort_unless($invoice->type === Document::TYPE_INVOICE, 404);

        $pdf = Pdf::loadView('invoices.print_do', ['invoice' => $invoice])->setPaper('a4');

        return $pdf->stream($this->safeFilename($invoice->doc_number) . '-DO.pdf');
    }

    /**
     * Bersihkan nomor dokumen (yang mengandung "/") supaya aman dipakai
     * sebagai nama file PDF. Contoh: "INV/IX/2026/0001" -> "INV-IX-2026-0001"
     */
    private function safeFilename(string $docNumber): string
    {
        return str_replace(['/', '\\'], '-', $docNumber);
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'doc_date'             => ['required', 'date'],
            'customer_name'        => ['required', 'string', 'max:255'],
            'customer_address'     => ['nullable', 'string'],
            'customer_phone'       => ['nullable', 'string', 'max:30'],
            'customer_npwp'        => ['nullable', 'string', 'max:40'],
            'customer_att'         => ['nullable', 'string', 'max:100'],
            'termin'               => ['nullable', 'string', 'max:100'],
            'discount'             => ['nullable', 'numeric', 'min:0'],
            'dp_amount'            => ['nullable', 'numeric', 'min:0'],
            'ppn_percent'          => ['nullable', 'numeric', 'min:0', 'max:100'],
            'shipping_cost'        => ['nullable', 'numeric', 'min:0'],
            'payment_status'       => ['required', Rule::in(array_keys(Document::STATUSES))],
            'payment_instruction'  => ['nullable', 'string'],
            'signed_by'            => ['nullable', 'string', 'max:255'],
            'signature'            => ['nullable', 'string'], // base64 dari signature_pad
            'notes'                => ['nullable', 'string'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.name'         => ['required', 'string'],
            'items.*.unit'         => ['nullable', 'string'],
            'items.*.qty'          => ['required', 'numeric', 'min:0.01'],
            'items.*.price'        => ['required', 'numeric', 'min:0'],
        ]);
    }

    private function normalizeItems(array $items): array
    {
        return collect($items)->map(function ($item) {
            $qty   = (float) ($item['qty'] ?? 0);
            $price = (float) ($item['price'] ?? 0);

            return [
                'name'  => $item['name'] ?? '',
                'unit'  => $item['unit'] ?? '',
                'qty'   => $qty,
                'price' => $price,
                'total' => round($qty * $price, 2),
            ];
        })->values()->all();
    }
}
