<?php

use App\Models\CustomerInvoicePosition;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnitPriceInformationToCustomerInvoicePositions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_invoice_positions', function (Blueprint $table) {
            $table->integer('tax_rate_percentage')->default(19)->after('unit_price');
            $table->double('net_amount')->default(0.00)->after('unit_price');
            $table->string('currency')->default('EUR')->after('unit_price');
        });
        Schema::table('customer_invoice_positions', function (Blueprint $table) {
            $table->dropColumn('unit_price');
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
            $table->json('unit_price')->nullable()->after('unit_name');
        });
        foreach([
                    'tax_rate_percentage',
                    'net_amount',
                    'currency'
                ] as $column) {
            if(Schema::hasColumn('customer_invoice_positions', $column)) {
                Schema::table('customer_invoice_positions', function (Blueprint $table) use($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
}
