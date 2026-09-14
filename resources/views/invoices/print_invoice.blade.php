<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 28px; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a1a1a; position: relative; }

    /* Watermark status di latar belakang */
    .watermark {
        position: fixed;
        top: 300px; left: 100px;
        font-size: 90px;
        font-weight: bold;
        color: {{ $invoice->stampColor() }};
        opacity: 0.07;
        transform: rotate(-25deg);
        z-index: -1;
    }

    /* ==== Kop surat ==== */
    .letterhead { width: 100%; margin-bottom: 6px; }
    .letterhead td { vertical-align: top; }
    .logo-box {
        width: 60px; height: 60px; border-radius: 8px;
        background: #1c3faa; color: #fff; text-align: center;
        font-weight: bold; font-size: 20px; line-height: 60px;
    }
    .company-name { font-size: 17px; font-weight: bold; color: #1c3faa; }
    .company-info-table { font-size: 10.5px; }
    .company-info-table td { padding: 1px 4px 1px 0; }
    .company-info-table td.label { width: 150px; }

    .invoice-title { text-align: right; font-size: 24px; font-weight: bold; letter-spacing: 2px; padding-top: 8px; }

    .double-line { border-top: 2px solid #1a1a1a; border-bottom: 2px solid #1a1a1a; height: 3px; margin: 8px 0 14px; }

    /* ==== Box customer & info invoice ==== */
    .info-row { width: 100%; margin-bottom: 14px; }
    .info-row td { vertical-align: top; width: 50%; }
    .box { border: 1px solid #999; padding: 8px 10px; }
    .box-title { font-weight: bold; font-style: italic; margin-bottom: 4px; }
    .kv-table { width: 100%; font-size: 10.5px; }
    .kv-table td { padding: 1px 0; vertical-align: top; }
    .kv-table td.k { width: 78px; }
    .kv-table td.sep { width: 10px; }

    /* ==== Tabel barang ==== */
    table.items { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    table.items th, table.items td { border: 1px solid #999; padding: 6px 8px; }
    table.items th { background: #eee; font-size: 10.5px; }
    .text-end { text-align: right; }
    .text-center { text-align: center; }

    /* ==== Say + ringkasan ==== */
    .bottom-row { width: 100%; margin-top: 4px; }
    .bottom-row td { vertical-align: top; }
    .say-box { border: 1px solid #999; padding: 8px 10px; width: 95%; }
    .totals { width: 100%; border-collapse: collapse; }
    .totals td { padding: 4px 8px; border: 1px solid #999; }
    .totals tr.grand td { font-weight: bold; }

    /* Stempel bulat transparan */
    .stamp {
        position: absolute;
        top: 130px; right: 40px;
        width: 100px; height: 100px;
        border: 4px solid {{ $invoice->stampColor() }};
        border-radius: 50%;
        color: {{ $invoice->stampColor() }};
        display: flex; align-items: center; justify-content: center;
        text-align: center; font-weight: bold; font-size: 14px;
        opacity: 0.75; transform: rotate(-12deg);
    }

    /* ==== Pembayaran & TTD ==== */
    .payment-section { margin-top: 16px; width: 100%; }
    .payment-section td { vertical-align: top; }
    .bank-title { font-weight: bold; font-style: italic; margin-top: 6px; }
    .bank-detail { padding-left: 14px; font-size: 10.5px; line-height: 1.5; }

    .signature-area { text-align: center; }
    .signature-area img { height: 55px; margin: 4px 0; }
    .signature-line { border-top: 1px solid #333; width: 170px; margin: 0 auto; padding-top: 4px; }
</style>
</head>
<body>
    <div class="watermark">{{ $invoice->statusLabel() }}</div>
    <div class="stamp">{{ $invoice->statusLabel() }}</div>

    {{-- ===== KOP SURAT ===== --}}
    <table class="letterhead">
        <tr>
            <td style="width:60px"><div class="logo-box">AR</div></td>
            <td style="width:calc(100% - 320px); padding-left:12px">
                <div class="company-name">TB. AR BAJA STEELINDO</div>
                <table class="company-info-table">
                    <tr><td class="label">Alamat Kantor</td><td>: Alamat Perusahaan Kamu Di Sini</td></tr>
                    <tr><td class="label">Telp</td><td>: 0000-0000-0000</td></tr>
                    <tr><td class="label">Web</td><td>: www.arbajasteelindo.co.id</td></tr>
                    <tr><td class="label">NPWP</td><td>: 00.000.000.0-000.000</td></tr>
                </table>
            </td>
            <td style="width:260px"><div class="invoice-title">INVOICE</div></td>
        </tr>
    </table>
    <div class="double-line"></div>

    {{-- ===== CUSTOMER & INFO INVOICE ===== --}}
    <table class="info-row">
        <tr>
            <td style="padding-right:8px">
                <div class="box">
                    <div class="box-title">CUSTOMER</div>
                    <table class="kv-table">
                        <tr><td class="k">Name</td><td class="sep">:</td><td><strong>{{ $invoice->customer_name }}</strong></td></tr>
                        <tr><td class="k">Address</td><td class="sep">:</td><td>{{ $invoice->customer_address ?? '-' }}</td></tr>
                        <tr><td class="k">NPWP</td><td class="sep">:</td><td>{{ $invoice->customer_npwp ?? '-' }}</td></tr>
                        <tr><td class="k">Phone</td><td class="sep">:</td><td>{{ $invoice->customer_phone ?? '-' }}</td></tr>
                        <tr><td class="k">Att.</td><td class="sep">:</td><td>{{ $invoice->customer_att ?? '-' }}</td></tr>
                    </table>
                </div>
            </td>
            <td style="padding-left:8px">
                <div class="box">
                    <table class="kv-table">
                        <tr><td class="k">Invoice No</td><td class="sep">:</td><td>{{ $invoice->doc_number }}</td></tr>
                        <tr><td class="k">Invoice Date</td><td class="sep">:</td><td>{{ $invoice->doc_date->format('d F Y') }}</td></tr>
                        <tr><td class="k">Termin</td><td class="sep">:</td><td>{{ $invoice->termin ?? '-' }}</td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- ===== TABEL BARANG ===== --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width:6%">No.</th>
                <th>Description</th>
                <th style="width:14%">Qty</th>
                <th style="width:18%">Unit Price (IDR)</th>
                <th style="width:20%">Total Price (IDR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $i => $item)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $item['name'] }}</td>
                <td class="text-center">{{ rtrim(rtrim(number_format($item['qty'], 2, ',', '.'), '0'), ',') }} {{ $item['unit'] }}</td>
                <td class="text-end">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ===== SAY (TERBILANG) + RINGKASAN BIAYA ===== --}}
    <table class="bottom-row">
        <tr>
            <td style="width:58%">
                <div class="say-box">
                    <strong>Say :</strong>
                    {{ ucfirst(\App\Models\Document::terbilang($invoice->grand_total)) }} Rupiah
                </div>
            </td>
            <td style="width:42%">
                <table class="totals">
                    <tr><td>Sub Total</td><td class="text-end">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td></tr>
                    <tr><td>Disc</td><td class="text-end">Rp {{ number_format($invoice->discount, 0, ',', '.') }}</td></tr>
                    <tr><td>Ongkos Kirim</td><td class="text-end">Rp {{ number_format($invoice->shipping_cost, 0, ',', '.') }}</td></tr>
                    <tr><td>Net Total</td><td class="text-end">Rp {{ number_format($invoice->netTotal(), 0, ',', '.') }}</td></tr>
                    <tr><td>Vat {{ rtrim(rtrim(number_format($invoice->ppn_percent,2,',','.'),'0'),',') }}%</td><td class="text-end">Rp {{ number_format($invoice->ppn_amount, 0, ',', '.') }}</td></tr>
                    <tr class="grand"><td>Grand Total</td><td class="text-end">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</td></tr>
                    <tr><td>DP / Uang Muka</td><td class="text-end">Rp {{ number_format($invoice->dp_amount, 0, ',', '.') }}</td></tr>
                    <tr class="grand" style="color:#dc3545"><td>Sisa Pembayaran</td><td class="text-end">Rp {{ number_format($invoice->remainingBalance(), 0, ',', '.') }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ===== INSTRUKSI PEMBAYARAN & TANDA TANGAN ===== --}}
    <table class="payment-section">
        <tr>
            <td style="width:60%">
                @if($invoice->payment_instruction)
                    <div><em><u>Please pay the Invoice :</u></em></div>
                    <div style="margin-top:4px; white-space:pre-line">{{ $invoice->payment_instruction }}</div>
                @else
                    <div><em><u>Please pay the Invoice :</u></em></div>
                    <div class="bank-title">Bank BCA</div>
                    <div class="bank-detail">
                        KCP Cabang Kamu<br>
                        Account Number IDR : 000.000.000<br>
                        Account Name : TB. AR Baja Steelindo
                    </div>
                @endif
                @if($invoice->notes)
                    <p style="margin-top:10px"><strong>Catatan:</strong> {{ $invoice->notes }}</p>
                @endif
            </td>
            <td style="width:40%" class="signature-area">
                @if($invoice->signature)
                    <img src="{{ $invoice->signature }}" alt="Tanda Tangan">
                @else
                    <div style="height:55px"></div>
                @endif
                <div class="signature-line"><u>{{ $invoice->signed_by ?? 'Kasir' }}</u><br>Finance</div>
            </td>
        </tr>
    </table>
</body>
</html>
