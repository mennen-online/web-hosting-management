<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerContact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateCustomerTest extends TestCase
{
    public function testCreatePersonCustomer() {
        $user = User::factory()
            ->has(
                $customer = Customer::factory()
                ->has($customerAddress = CustomerAddress::factory(), 'address')
                ->has($customerContact = CustomerContact::factory(), 'contacts')
            )->create();

        $this->assertModelExists($user);

        $this->assertModelExists($user->customer);

        $this->assertModelExists($user->customer->address);

        $this->assertModelExists($user->customer->contacts()->first());
    }

    public function testCreateCompanyCustomer() {
        $user = User::factory()
            ->has($customer = Customer::factory()->company()
                ->has($customerAddress = CustomerAddress::factory(), 'address')
                ->has($customerContact = CustomerContact::factory(), 'contacts')
            )->create();

        $this->assertModelExists($user);

        $this->assertModelExists($user->customer);

        $this->assertModelExists($user->customer->address);

        $this->assertModelExists($user->customer->contacts()->first());
    }
}
