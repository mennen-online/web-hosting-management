<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVoucherDateAndPaymentTermDurationToCustomerInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->renameColumn('totalNetAmount', 'total_net_amount');
            $table->renameColumn('totalGrossAmount', 'total_gross_amount');
            $table->renameColumn('totalTaxAmount', 'total_tax_amount');
            $table->renameColumn('voucherNumber', 'voucher_number');
            $table->integer('payment_term_duration')->nullable()->default(null)->after('totalTaxAmount');
            $table->date('voucher_date')->nullable()->default(null)->after('totalTaxAmount');
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
                'payment_term_duration',
                'voucher_date'
            ]);
            $table->renameColumn('total_net_amount', 'totalNetAmount');
            $table->renameColumn('total_gross_amount', 'totalGrossAmount');
            $table->renameColumn('total_tax_amount', 'totalTaxAmount');
        });
    }
}
