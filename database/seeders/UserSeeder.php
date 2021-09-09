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
        $role = Role::byName('Administrator');

        $user = User::factory()->create([
            'email' => 'admin@nova.com'
        ]);

        $user->roles()->using($role);
    }
}
