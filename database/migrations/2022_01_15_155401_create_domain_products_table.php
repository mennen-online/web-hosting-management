<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDomainProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('domain_products', function (Blueprint $table) {
            $table->id();
            $table->string('tld');
            $table->string('currency');
            $table->boolean('promo')->default(false);
            $table->double('promo_price')->default(0.00);
            $table->string('promo_type')->default('');
            $table->enum('period', [
                '1M',
                '1Y',
                '2Y',
                '3Y',
                '4Y',
                '5Y',
                '6Y',
                '7Y',
                '8Y',
                '9Y',
                '10Y'
            ])->default('1Y');
            $table->double('reg_price', 10, 2, true);
            $table->double('renewal_price', 10, 2, true);
            $table->double('update_price', 10, 2, true);
            $table->double('restore_price', 10, 2, true);
            $table->double('transfer_price', 10, 2, true);
            $table->double('trade_price', 10, 2, true);
            $table->double('whois_protection_price', 10, 2, true)->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('domain_products');
    }
}
