<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\CustomerInvoice;
use App\Models\CustomerProduct;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $administrator = Role::create(['name' => 'Administrator']);

        $customer = Role::create(['name' => 'Customer']);



        Permission::all()->each(function($permission) use($administrator) {
            $permission->roles()->attach($administrator);
        });

        Permission::whereIn('name', [
            'view-' . Customer::class,
            'view-' . CustomerInvoice::class,
            'view-' . CustomerProduct::class,
            'view-' . CustomerContact::class,
            'view-' . User::class,
            'store-' . CustomerProduct::class,
            'delete-' . CustomerProduct::class
        ])->get()->each(function($permission) use($customer) {
            $permission->roles()->attach($customer);
        });
    }
}
