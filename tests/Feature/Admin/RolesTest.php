<?php

namespace Tests\Feature\Admin;

use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\CustomerInvoice;
use App\Models\CustomerProduct;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RolesTest extends TestCase
{
    public function testAdministratorRole() {
        $this->assertEquals(Permission::all()->count(), Role::byName('Administrator')->permissions()->count());
    }

    public function testCustomerRole() {
        $this->assertEquals(Permission::whereIn('name', [
            'view-' . Customer::class,
            'view-' . CustomerInvoice::class,
            'view-' . CustomerProduct::class,
            'view-' . CustomerContact::class,
            'view-' . User::class,
            'store-' . CustomerProduct::class,
            'delete-' . CustomerProduct::class
        ])->get()->count(), Role::byName('Customer')->permissions()->count());
    }
}
