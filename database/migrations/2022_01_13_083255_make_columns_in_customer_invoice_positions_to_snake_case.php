<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeColumnsInCustomerInvoicePositionsToSnakeCase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('customer_invoice_positions', function (Blueprint $table) {
            $table->renameColumn('unitName', 'unit_name');
        });
        Schema::table('customer_invoice_positions', function (Blueprint $table) {
            $table->renameColumn('unitPrice', 'unit_price');
        });
        Schema::table('customer_invoice_positions', function (Blueprint $table) {
            $table->renameColumn('discountPercentage', 'discount_percentage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('customer_invoice_positions', function (Blueprint $table) {
            $table->renameColumn('unit_name', 'unitName');
        });
        Schema::table('customer_invoice_positions', function (Blueprint $table) {
            $table->renameColumn('unit_price', 'unitPrice');
        });
        Schema::table('customer_invoice_positions', function (Blueprint $table) {
            $table->renameColumn('discount_percentage', 'discountPercentage');
        });
    }
}
