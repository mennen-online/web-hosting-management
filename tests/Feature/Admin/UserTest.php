<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\TestCase;

class UserTest extends TestCase
{
    protected User $administrator;

    protected User $customer;

    protected function setUp(): void {
        parent::setUp();

        $this->administrator = User::factory()->hasAttached(Role::byName('Administrator'))->create(['email' => 'admin@app.com']);

        $this->customer = User::factory()
            ->hasAttached(Role::byName('Customer'))->create(['email' => 'customer@app.com']);
    }

    public function testAdminUserHasAdminRights() {
        $this->assertDatabaseHas('users', Arr::except($this->administrator->toArray(), ['created_at', 'updated_at', 'email_verified_at']));

        $this->assertTrue(User::where('email', 'admin@app.com')->whereHas('roles', function($query) {
            $query->where('name', 'Administrator');
        })->exists());
    }

    public function testCustomerUserHasOnlyViewRights() {
        $this->assertDatabaseHas('users', Arr::except($this->customer->toArray(), ['created_at', 'updated_at', 'email_verified_at']));

        $this->assertTrue(
            User::where('email', 'customer@app.com')->whereHas('roles', function($query) {
                $query->where('name', 'Customer');
            })->exists()
        );

    }
}
