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
        Schema::table('customer_invoices', function(Blueprint $table) {
            $table->renameColumn('totalNetAmount', 'total_net_amount');
        });
        Schema::table('customer_invoices', function(Blueprint $table) {
            $table->renameColumn('totalGrossAmount', 'total_gross_amount');
        });
        Schema::table('customer_invoices', function(Blueprint $table) {
            $table->renameColumn('totalTaxAmount', 'total_tax_amount');
        });
        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->renameColumn('voucherNumber', 'voucher_number');
        });
        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->integer('payment_term_duration')->nullable()->default(null)->after('total_tax_amount');
            $table->date('voucher_date')->nullable()->default(null)->after('total_tax_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_invoices', function(Blueprint $table) {
            foreach([
                'payment_term_duration',
                'voucher_date'
            ] as $column) {
                if(Schema::hasColumn('customer_invoices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->renameColumn('total_net_amount', 'totalNetAmount');
        });
        Schema::table('customer_invoices', function(Blueprint $table) {
            $table->renameColumn('total_gross_amount', 'totalGrossAmount');
        });
        Schema::table('customer_invoices', function(Blueprint $table) {
            $table->renameColumn('total_tax_amount', 'totalTaxAmount');
        });
    }
}
