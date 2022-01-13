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
    public function up()
    {
        Schema::table('customer_invoice_positions', function (Blueprint $table) {
            $table->renameColumn('unitName', 'unit_name');
            $table->renameColumn('unitPrice', 'unit_price');
            $table->renameColumn('discountPercentage', 'discount_percentage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_invoice_positions', function (Blueprint $table) {
            $table->renameColumn('unit_name', 'unitName');
            $table->renameColumn('unit_price', 'unitPrice');
            $table->renameColumn('discount_percentage', 'discountPercentage');
        });
    }
}
