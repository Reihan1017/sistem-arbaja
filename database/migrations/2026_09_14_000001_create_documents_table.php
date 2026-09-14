<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            // Jenis dokumen: surat_keluar, surat_dukungan, quotation, invoice
            $table->string('type', 30)->index();

            // Nomor & tanggal dokumen
            $table->string('doc_number', 60)->unique();
            $table->date('doc_date');

            // Data pelanggan / tujuan surat
            $table->string('customer_name');
            $table->text('customer_address')->nullable();
            $table->string('customer_phone', 30)->nullable();

            // Untuk Surat Keluar & Surat Dukungan
            $table->string('subject')->nullable();      // Perihal
            $table->longText('body')->nullable();        // Isi surat

            // Untuk Quotation & Invoice
            $table->json('items')->nullable();           // [{name, qty, unit, price, total}, ...]
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('ppn_percent', 5, 2)->default(0);
            $table->decimal('ppn_amount', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0); // Ongkos Kirim
            $table->decimal('grand_total', 15, 2)->default(0);

            // Khusus Invoice
            $table->string('payment_status', 20)->default('DRAFT')->index(); // DRAFT, LUNAS, COD, TEMPO, BATAL
            $table->text('payment_instruction')->nullable();
            $table->string('signed_by')->nullable();      // Nama kasir
            $table->longText('signature')->nullable();    // Base64 PNG tanda tangan

            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
