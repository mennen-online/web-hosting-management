<?php

use App\Models\CustomerInvoice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerInvoicePositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_invoice_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(CustomerInvoice::class);
            $table->string('type');
            $table->string('name');
            $table->string('description')->nullable()->default(null);
            $table->string('unitName');
            $table->json('unitPrice');
            $table->double('discountPercentage');
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
        Schema::dropIfExists('customer_invoice_positions');
    }
}
