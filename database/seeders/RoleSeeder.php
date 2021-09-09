<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
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
        $role = Role::create(['name' => 'Administrator']);

        Permission::all()->each(function($permission) use($role) {
            $permission->roles()->attach($role);
        });

        $role = Role::create(['name' => 'Customer']);
    }
}
