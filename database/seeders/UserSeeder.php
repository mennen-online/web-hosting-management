<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $administratorRole = Role::byName('Administrator');

        $administrator = User::factory()->create([
            'email' => 'admin@nova.com'
        ]);

        $administrator->roles()->attach($administratorRole);

        $customerRole = Role::byName('Customer');

        $customer = User::factory()->create([
            'email' => 'customer@nova.com'
        ]);

        $customer->roles()->attach($customerRole);
    }
}
