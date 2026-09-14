<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #222; }
    .header { text-align: center; border-bottom: 3px solid #222; padding-bottom: 10px; margin-bottom: 20px; }
    .header h1 { margin: 0; font-size: 20px; }
    table.items { width: 100%; border-collapse: collapse; margin: 15px 0; }
    table.items th, table.items td { border: 1px solid #999; padding: 6px 8px; }
    table.items th { background: #eee; }
    .text-end { text-align: right; }
    .totals { width: 45%; margin-left: auto; border-collapse: collapse; }
    .totals td { padding: 4px 8px; }
    .totals .grand { font-weight: bold; font-size: 14px; border-top: 2px solid #222; }
</style>
</head>
<body>
    <div class="header">
        <h1>TB. AR BAJA STEELINDO</h1>
        <p>Surat Penawaran Harga (Quotation)</p>
    </div>

    <table style="width:100%; margin-bottom: 15px;">
        <tr>
            <td style="width:70%">
                No: <strong>{{ $document->doc_number }}</strong><br>
                Kepada: <strong>{{ $document->customer_name }}</strong><br>
                {{ $document->customer_address }}
            </td>
            <td style="width:30%; text-align:right; vertical-align:top">
                Tanggal: {{ $document->doc_date->format('d F Y') }}
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>#</th><th>Nama Barang</th><th>Satuan</th><th>Qty</th><th>Harga Satuan</th><th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($document->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item['name'] }}</td>
                <td>{{ $item['unit'] }}</td>
                <td class="text-end">{{ rtrim(rtrim(number_format($item['qty'], 2, ',', '.'), '0'), ',') }}</td>
                <td class="text-end">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="text-end">Rp {{ number_format($document->subtotal, 0, ',', '.') }}</td></tr>
        <tr><td>PPN ({{ rtrim(rtrim(number_format($document->ppn_percent,2,',','.'),'0'),',') }}%)</td><td class="text-end">Rp {{ number_format($document->ppn_amount, 0, ',', '.') }}</td></tr>
        <tr><td>Ongkos Kirim</td><td class="text-end">Rp {{ number_format($document->shipping_cost, 0, ',', '.') }}</td></tr>
        <tr class="grand"><td>Grand Total</td><td class="text-end">Rp {{ number_format($document->grand_total, 0, ',', '.') }}</td></tr>
    </table>

    @if($document->notes)
        <p><strong>Catatan:</strong> {{ $document->notes }}</p>
    @endif
</body>
</html>
