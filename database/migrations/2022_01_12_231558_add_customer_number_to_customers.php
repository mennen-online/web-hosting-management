<?php

use App\Models\Customer;
use App\Services\Lexoffice\Endpoints\ContactsEndpoint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerNumberToCustomers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('number')->default("")->after('lexoffice_id');
        });

        app()->make(ContactsEndpoint::class)->index()->each(function($customer) {
            if($customer?->roles?->customer?->number) {
                Customer::where('lexoffice_id', $customer->id)->update(['number' => $customer->roles->customer->number]);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('number');
        });
    }
}
