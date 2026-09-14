<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['po_number', 'po_date', 'sales_name']);
            $table->decimal('dp_amount', 15, 2)->default(0)->after('discount'); // Uang muka / DP yang sudah dibayar
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['dp_amount']);
            $table->string('po_number', 60)->nullable();
            $table->date('po_date')->nullable();
            $table->string('sales_name', 100)->nullable();
        });
    }
};
