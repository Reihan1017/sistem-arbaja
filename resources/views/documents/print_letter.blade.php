<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #222; }
    .header { text-align: center; border-bottom: 3px solid #222; padding-bottom: 10px; margin-bottom: 20px; }
    .header h1 { margin: 0; font-size: 20px; }
    .header p { margin: 2px 0; font-size: 11px; }
    .meta { width: 100%; margin-bottom: 15px; }
    .meta td { padding: 2px 0; vertical-align: top; }
    .body-text { line-height: 1.7; text-align: justify; white-space: pre-line; margin: 20px 0; }
    .signature { margin-top: 60px; width: 100%; }
    .signature td { text-align: center; width: 50%; }
</style>
</head>
<body>
    <div class="header">
        <h1>TB. AR BAJA STEELINDO</h1>
        <p>Distributor & Supplier Besi / Baja Konstruksi</p>
        <p>Alamat Perusahaan — Telp: 0000-0000-0000</p>
    </div>

    <table class="meta">
        <tr>
            <td style="width:70%">
                <strong>{{ $document->typeLabel() }}</strong><br>
                No: {{ $document->doc_number }}
            </td>
            <td style="width:30%; text-align:right">
                Tanggal: {{ $document->doc_date->format('d F Y') }}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                Kepada Yth,<br>
                <strong>{{ $document->customer_name }}</strong><br>
                {{ $document->customer_address }}<br>
                @if($document->customer_phone) Telp: {{ $document->customer_phone }} @endif
            </td>
        </tr>
        @if($document->subject)
        <tr><td colspan="2">Perihal: <strong>{{ $document->subject }}</strong></td></tr>
        @endif
    </table>

    <div class="body-text">{{ $document->body }}</div>

    <table class="signature">
        <tr>
            <td></td>
            <td>
                Hormat kami,<br><br><br><br>
                <strong>TB. AR Baja Steelindo</strong>
            </td>
        </tr>
    </table>
</body>
</html>
