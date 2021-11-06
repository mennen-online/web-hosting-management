<?php

use App\Models\CustomerInvoice;
use App\Models\CustomerProduct;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CustomerInvoicesCustomerProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('customer_invoices_customer_products', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignIdFor(CustomerInvoice::class);
            $blueprint->foreignIdFor(CustomerProduct::class);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('customer_invoices_customer_products');
    }
}
