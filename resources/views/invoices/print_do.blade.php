<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 28px; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a1a1a; }

    /* ==== Kop surat (konsisten dengan invoice) ==== */
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

    .doc-title { text-align: right; font-size: 19px; font-weight: bold; letter-spacing: 1px; padding-top: 12px; line-height: 1.3; }

    .double-line { border-top: 2px solid #1a1a1a; border-bottom: 2px solid #1a1a1a; height: 3px; margin: 8px 0 14px; }

    /* ==== Box info ==== */
    .info-row { width: 100%; margin-bottom: 16px; }
    .info-row td { vertical-align: top; width: 50%; }
    .box { border: 1px solid #999; padding: 8px 10px; }
    .box-title { font-weight: bold; font-style: italic; margin-bottom: 4px; }
    .kv-table { width: 100%; font-size: 10.5px; }
    .kv-table td { padding: 1px 0; vertical-align: top; }
    .kv-table td.k { width: 90px; }
    .kv-table td.sep { width: 10px; }

    /* ==== Tabel barang (tanpa harga) ==== */
    table.items { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    table.items th, table.items td { border: 1px solid #999; padding: 7px 8px; }
    table.items th { background: #eee; font-size: 10.5px; }
    .text-center { text-align: center; }

    .notice {
        border: 1px dashed #999; background: #fafafa; padding: 8px 10px;
        font-size: 10.5px; font-style: italic; margin-bottom: 10px;
    }

    /* ==== Tanda tangan 3 kolom ==== */
    .sign-table { width: 100%; margin-top: 50px; border-collapse: collapse; }
    .sign-table td { text-align: center; width: 33.33%; vertical-align: bottom; padding: 0 10px; }
    .sign-box { height: 65px; }
    .sign-line { border-top: 1px solid #333; padding-top: 4px; margin-top: 6px; font-weight: bold; }
    .sign-sub { font-size: 9.5px; color: #555; }
</style>
</head>
<body>
    {{-- ===== KOP SURAT ===== --}}
    <table class="letterhead">
        <tr>
            <td style="width:60px"><div class="logo-box">AR</div></td>
            <td style="width:calc(100% - 300px); padding-left:12px">
                <div class="company-name">TB. AR BAJA STEELINDO</div>
                <table class="company-info-table">
                    <tr><td class="label">Alamat Kantor</td><td>: Alamat Perusahaan Kamu Di Sini</td></tr>
                    <tr><td class="label">Telp</td><td>: 0000-0000-0000</td></tr>
                    <tr><td class="label">Web</td><td>: www.arbajasteelindo.co.id</td></tr>
                </table>
            </td>
            <td style="width:240px"><div class="doc-title">SURAT JALAN<br><span style="font-size:12px">(Delivery Order)</span></div></td>
        </tr>
    </table>
    <div class="double-line"></div>

    {{-- ===== INFO PENGIRIMAN ===== --}}
    <table class="info-row">
        <tr>
            <td style="padding-right:8px">
                <div class="box">
                    <div class="box-title">DIKIRIM KEPADA</div>
                    <table class="kv-table">
                        <tr><td class="k">Nama</td><td class="sep">:</td><td><strong>{{ $invoice->customer_name }}</strong></td></tr>
                        <tr><td class="k">Alamat</td><td class="sep">:</td><td>{{ $invoice->customer_address ?? '-' }}</td></tr>
                        <tr><td class="k">Telp</td><td class="sep">:</td><td>{{ $invoice->customer_phone ?? '-' }}</td></tr>
                        <tr><td class="k">Att.</td><td class="sep">:</td><td>{{ $invoice->customer_att ?? '-' }}</td></tr>
                    </table>
                </div>
            </td>
            <td style="padding-left:8px">
                <div class="box">
                    <table class="kv-table">
                        <tr><td class="k">No. Surat Jalan</td><td class="sep">:</td><td>{{ $invoice->doc_number }}</td></tr>
                        <tr><td class="k">Tanggal Kirim</td><td class="sep">:</td><td>{{ $invoice->doc_date->format('d F Y') }}</td></tr>
                        <tr><td class="k">Ref. Invoice</td><td class="sep">:</td><td>{{ $invoice->doc_number }}</td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- ===== TABEL BARANG (data identik dengan Invoice, tanpa harga) ===== --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width:8%">No.</th>
                <th>Nama Barang</th>
                <th style="width:18%">Satuan</th>
                <th style="width:18%">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $i => $item)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $item['name'] }}</td>
                <td class="text-center">{{ $item['unit'] }}</td>
                <td class="text-center">{{ rtrim(rtrim(number_format($item['qty'], 2, ',', '.'), '0'), ',') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="notice">
        Barang di atas telah diperiksa dan diterima dalam kondisi baik dan lengkap sesuai jumlah yang tercantum.
    </div>

    @if($invoice->notes)
        <p><strong>Catatan Pengiriman:</strong> {{ $invoice->notes }}</p>
    @endif

    {{-- ===== TANDA TANGAN SOPIR / GUDANG / PENERIMA ===== --}}
    <table class="sign-table">
        <tr>
            <td>
                <div class="sign-box"></div>
                <div class="sign-line">Sopir</div>
                <div class="sign-sub">Nama & Tanda Tangan</div>
            </td>
            <td>
                <div class="sign-box"></div>
                <div class="sign-line">Gudang</div>
                <div class="sign-sub">Nama & Tanda Tangan</div>
            </td>
            <td>
                <div class="sign-box"></div>
                <div class="sign-line">Penerima</div>
                <div class="sign-sub">Nama, Tanda Tangan & Stempel</div>
            </td>
        </tr>
    </table>
</body>
</html>
