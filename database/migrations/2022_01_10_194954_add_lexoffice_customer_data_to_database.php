<?php

use App\Models\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLexofficeCustomerDataToDatabase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_contacts', function(Blueprint $blueprint) {
            $blueprint->boolean('primary')->default(false)->after('last_name');
        });

        Schema::table('customers', function(Blueprint $blueprint) {
            $blueprint->string('phone')->after('lexoffice_id')->nullable()->default(null);
            $blueprint->string('email')->after('lexoffice_id')->nullable()->default(null);
            $blueprint->string('last_name')->default('')->after('lexoffice_id');
            $blueprint->string('first_name')->default('')->after('lexoffice_id');
            $blueprint->string('salutation', 5)->default('')->after('lexoffice_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_contacts', function (Blueprint $table) {
            $table->dropColumn('primary');
        });

        Schema::table('customers', function(Blueprint $blueprint) {
            $blueprint->dropColumn([
                'salutation',
                'first_name',
                'last_name',
                'email',
                'phone'
            ]);
        });
    }
}
