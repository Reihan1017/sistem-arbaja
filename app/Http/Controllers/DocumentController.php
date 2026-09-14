<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentController extends Controller
{
    /** Tipe yang ditangani controller ini (bukan invoice) */
    private array $allowedTypes = [
        Document::TYPE_SURAT_KELUAR,
        Document::TYPE_SURAT_DUKUNGAN,
        Document::TYPE_QUOTATION,
    ];

    public function index(Request $request)
    {
        $type = $request->get('type', Document::TYPE_SURAT_KELUAR);

        abort_unless(in_array($type, $this->allowedTypes), 404);

        $documents = Document::type($type)
            ->search($request->get('q'))
            ->latest('doc_date')
            ->paginate(15)
            ->withQueryString();

        return view('documents.index', [
            'documents'  => $documents,
            'activeType' => $type,
            'types'      => collect($this->allowedTypes)->mapWithKeys(fn ($t) => [$t => Document::TYPES[$t]]),
        ]);
    }

    public function create(string $type)
    {
        abort_unless(in_array($type, $this->allowedTypes), 404);

        return view('documents.create', [
            'type'     => $type,
            'document' => new Document(['type' => $type, 'doc_date' => now()->toDateString()]),
        ]);
    }

    public function store(Request $request)
    {
        $type = $request->input('type');
        abort_unless(in_array($type, $this->allowedTypes), 404);

        $validated = $this->validateRequest($request, $type);
        $validated['type'] = $type;
        $validated['doc_number'] = Document::generateDocNumber($type);
        $validated['created_by'] = Auth::id();

        $document = new Document($validated);

        if ($type === Document::TYPE_QUOTATION) {
            $document->items = $this->normalizeItems($request->input('items', []));
            $document->recalculateTotals();
        }

        $document->save();

        return redirect()
            ->route('documents.index', ['type' => $type])
            ->with('success', 'Dokumen berhasil dibuat: ' . $document->doc_number);
    }

    public function edit(Document $document)
    {
        abort_unless(in_array($document->type, $this->allowedTypes), 404);

        return view('documents.create', [
            'type'     => $document->type,
            'document' => $document,
        ]);
    }

    public function update(Request $request, Document $document)
    {
        abort_unless(in_array($document->type, $this->allowedTypes), 404);

        $validated = $this->validateRequest($request, $document->type);
        $document->fill($validated);

        if ($document->type === Document::TYPE_QUOTATION) {
            $document->items = $this->normalizeItems($request->input('items', []));
            $document->recalculateTotals();
        }

        $document->save();

        return redirect()
            ->route('documents.index', ['type' => $document->type])
            ->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(Document $document)
    {
        abort_unless(in_array($document->type, $this->allowedTypes), 404);

        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function print(Document $document)
    {
        abort_unless(in_array($document->type, $this->allowedTypes), 404);

        $view = $document->type === Document::TYPE_QUOTATION
            ? 'documents.print_quotation'
            : 'documents.print_letter';

        $pdf = Pdf::loadView($view, ['document' => $document])->setPaper('a4');

        return $pdf->stream(str_replace(['/', '\\'], '-', $document->doc_number) . '.pdf');
    }

    private function validateRequest(Request $request, string $type): array
    {
        $rules = [
            'doc_date'         => ['required', 'date'],
            'customer_name'    => ['required', 'string', 'max:255'],
            'customer_address' => ['nullable', 'string'],
            'customer_phone'   => ['nullable', 'string', 'max:30'],
            'subject'          => ['nullable', 'string', 'max:255'],
            'notes'            => ['nullable', 'string'],
        ];

        if (in_array($type, [Document::TYPE_SURAT_KELUAR, Document::TYPE_SURAT_DUKUNGAN])) {
            $rules['body'] = ['required', 'string'];
        }

        if ($type === Document::TYPE_QUOTATION) {
            $rules['ppn_percent'] = ['nullable', 'numeric', 'min:0', 'max:100'];
            $rules['shipping_cost'] = ['nullable', 'numeric', 'min:0'];
            $rules['items'] = ['required', 'array', 'min:1'];
            $rules['items.*.name'] = ['required', 'string'];
            $rules['items.*.qty'] = ['required', 'numeric', 'min:0.01'];
            $rules['items.*.price'] = ['required', 'numeric', 'min:0'];
        }

        return $request->validate($rules);
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
