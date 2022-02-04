<?php

use Doctrine\DBAL\Types\FloatType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeUnitNameInCustomerInvoicePositionsNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_invoice_positions', function (Blueprint $table) {
            $table->string('unit_name')->nullable()->default(null)->change();
            if(!\Doctrine\DBAL\Types\Type::hasType('double')) {
                \Doctrine\DBAL\Types\Type::addType('double', FloatType::class);
            }
            $table->double('discount_percentage')->nullable()->default(null)->change();
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
            $table->string('unit_name')->nullable(false)->change();
            if(!\Doctrine\DBAL\Types\Type::hasType('double')) {
                \Doctrine\DBAL\Types\Type::addType('double', FloatType::class);
            }
            $table->double('discount_percentage')->nullable(false)->change();
        });
    }
}
