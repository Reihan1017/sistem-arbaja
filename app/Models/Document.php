<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    // ==== Konstanta tipe dokumen ====
    const TYPE_SURAT_KELUAR   = 'surat_keluar';
    const TYPE_SURAT_DUKUNGAN = 'surat_dukungan';
    const TYPE_QUOTATION      = 'quotation';
    const TYPE_INVOICE        = 'invoice';

    const TYPES = [
        self::TYPE_SURAT_KELUAR   => 'Surat Keluar',
        self::TYPE_SURAT_DUKUNGAN => 'Surat Dukungan',
        self::TYPE_QUOTATION      => 'Quotation / Penawaran',
        self::TYPE_INVOICE        => 'Invoice',
    ];

    // ==== Konstanta status pembayaran (khusus invoice) ====
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_LUNAS = 'LUNAS';
    const STATUS_COD   = 'COD';
    const STATUS_TEMPO = 'TEMPO';
    const STATUS_BATAL = 'BATAL';

    const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_LUNAS => 'Lunas',
        self::STATUS_COD   => 'COD',
        self::STATUS_TEMPO => 'Tempo',
        self::STATUS_BATAL => 'Batal',
    ];

    protected $fillable = [
        'type', 'doc_number', 'doc_date',
        'customer_name', 'customer_address', 'customer_phone', 'customer_npwp', 'customer_att',
        'subject', 'body', 'termin',
        'items', 'subtotal', 'discount', 'ppn_percent', 'ppn_amount', 'shipping_cost',
        'dp_amount', 'grand_total',
        'payment_status', 'payment_instruction', 'signed_by', 'signature',
        'notes', 'created_by',
    ];

    protected $casts = [
        'doc_date'      => 'date',
        'items'         => 'array',
        'subtotal'      => 'decimal:2',
        'discount'      => 'decimal:2',
        'ppn_percent'   => 'decimal:2',
        'ppn_amount'    => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'dp_amount'     => 'decimal:2',
        'grand_total'   => 'decimal:2',
    ];

    // ==== Relasi ====
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // ==== Scopes ====
    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('doc_number', 'like', "%{$term}%")
              ->orWhere('customer_name', 'like', "%{$term}%")
              ->orWhere('subject', 'like', "%{$term}%");
        });
    }

    // ==== Helper tampilan ====
    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->payment_status] ?? $this->payment_status;
    }

    /**
     * Warna badge Bootstrap sesuai status pembayaran.
     */
    public function statusBadgeColor(): string
    {
        return match ($this->payment_status) {
            self::STATUS_LUNAS => 'success',   // Hijau
            self::STATUS_COD   => 'primary',   // Biru
            self::STATUS_TEMPO => 'warning',   // Oranye
            self::STATUS_BATAL => 'danger',    // Merah
            default             => 'secondary', // Draft = abu-abu
        };
    }

    /**
     * Warna stempel transparan di PDF (hex, dipakai lewat CSS).
     */
    public function stampColor(): string
    {
        return match ($this->payment_status) {
            self::STATUS_LUNAS => '#198754',
            self::STATUS_COD   => '#0d6efd',
            self::STATUS_TEMPO => '#fd7e14',
            self::STATUS_BATAL => '#dc3545',
            default             => '#6c757d',
        };
    }

    /**
     * Hitung ulang subtotal, ppn_amount, dan grand_total dari items + diskon + ppn_percent + shipping_cost.
     * Urutan: Subtotal -> dikurangi Diskon -> Net -> ditambah PPN -> ditambah Ongkir -> Grand Total.
     * Panggil sebelum save() setiap kali items berubah.
     */
    public function recalculateTotals(): void
    {
        $items = collect($this->items ?? []);

        $subtotal = $items->sum(function ($item) {
            return (float) ($item['qty'] ?? 0) * (float) ($item['price'] ?? 0);
        });

        $netTotal   = $subtotal - (float) $this->discount;
        $ppnAmount  = $netTotal * ((float) $this->ppn_percent / 100);
        $grandTotal = $netTotal + $ppnAmount + (float) $this->shipping_cost;

        $this->subtotal    = round($subtotal, 2);
        $this->ppn_amount  = round($ppnAmount, 2);
        $this->grand_total = round($grandTotal, 2);
    }

    public function netTotal(): float
    {
        return round((float) $this->subtotal - (float) $this->discount, 2);
    }

    /**
     * Sisa pembayaran = Grand Total - DP yang sudah dibayar.
     */
    public function remainingBalance(): float
    {
        return round((float) $this->grand_total - (float) $this->dp_amount, 2);
    }

    /**
     * Ubah angka menjadi kalimat terbilang Bahasa Indonesia.
     * Contoh: 54729000 -> "lima puluh empat juta tujuh ratus dua puluh sembilan ribu"
     */
    public static function terbilang(float $number): string
    {
        $number = (int) round($number);

        if ($number < 0) {
            return 'minus ' . self::terbilang(abs($number));
        }

        $words = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan',
                   'sepuluh', 'sebelas'];

        if ($number < 12) {
            return $words[$number];
        } elseif ($number < 20) {
            return self::terbilang($number - 10) . ' belas';
        } elseif ($number < 100) {
            return trim(self::terbilang((int) ($number / 10)) . ' puluh ' . self::terbilang($number % 10));
        } elseif ($number < 200) {
            return trim('seratus ' . self::terbilang($number - 100));
        } elseif ($number < 1000) {
            return trim(self::terbilang((int) ($number / 100)) . ' ratus ' . self::terbilang($number % 100));
        } elseif ($number < 2000) {
            return trim('seribu ' . self::terbilang($number - 1000));
        } elseif ($number < 1000000) {
            return trim(self::terbilang((int) ($number / 1000)) . ' ribu ' . self::terbilang($number % 1000));
        } elseif ($number < 1000000000) {
            return trim(self::terbilang((int) ($number / 1000000)) . ' juta ' . self::terbilang($number % 1000000));
        } elseif ($number < 1000000000000) {
            return trim(self::terbilang((int) ($number / 1000000000)) . ' miliar ' . self::terbilang($number % 1000000000));
        }

        return trim(self::terbilang((int) ($number / 1000000000000)) . ' triliun ' . self::terbilang($number % 1000000000000));
    }

    /**
     * Nomor dokumen otomatis, format: PREFIX/BULAN-ROMAWI/TAHUN/URUT
     * Contoh: INV/IX/2026/0001
     */
    public static function generateDocNumber(string $type): string
    {
        $prefixes = [
            self::TYPE_SURAT_KELUAR   => 'SK',
            self::TYPE_SURAT_DUKUNGAN => 'SD',
            self::TYPE_QUOTATION      => 'QUO',
            self::TYPE_INVOICE        => 'INV',
        ];

        $prefix = $prefixes[$type] ?? 'DOC';
        $roman  = self::toRoman((int) now()->format('n'));
        $year   = now()->format('Y');

        $count = self::where('type', $type)
            ->whereYear('created_at', now()->year)
            ->count() + 1;

        $sequence = str_pad((string) $count, 4, '0', STR_PAD_LEFT);

        return "{$prefix}/{$roman}/{$year}/{$sequence}";
    }

    private static function toRoman(int $month): string
    {
        $map = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        return $map[$month - 1] ?? (string) $month;
    }
}
