<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceDataToCustomerInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->double('totalTaxAmount')->after('lexoffice_id');
            $table->double('totalGrossAmount')->after('lexoffice_id');
            $table->double('totalNetAmount')->after('lexoffice_id');
            $table->string('voucherNumber')->after('lexoffice_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->dropColumn([
                'totalTaxAmount',
                'totalGrossAmount',
                'totalNetAmount',
                'voucherNumber'
            ]);
        });
    }
}
