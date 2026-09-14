<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('po_number', 60)->nullable()->after('subject');   // No. SPK / PO
            $table->date('po_date')->nullable()->after('po_number');
            $table->string('sales_name', 100)->nullable()->after('po_date');
            $table->string('termin', 100)->nullable()->after('sales_name'); // Contoh: "DP 20% 80% CBD"
            $table->decimal('discount', 15, 2)->default(0)->after('subtotal');
            $table->string('customer_npwp', 40)->nullable()->after('customer_phone');
            $table->string('customer_att', 100)->nullable()->after('customer_npwp'); // Attention / PIC
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'po_number', 'po_date', 'sales_name', 'termin',
                'discount', 'customer_npwp', 'customer_att',
            ]);
        });
    }
};
