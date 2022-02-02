<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerContact;
use App\Models\User;
use App\Services\Lexoffice\Endpoints\ContactsEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CreateCustomerTest extends TestCase
{
    use WithFaker;

    public function testCreatePersonCustomer()
    {
        $customer = Customer::factory()
            ->for(User::factory(), 'user')
            ->has($customerAddress = CustomerAddress::factory(), 'address')
            ->make();

        $customerContact = CustomerContact::factory()
            ->for($customer)->make();

        $uuid = $this->faker->uuid;

        Http::fake([
            'https://api.lexoffice.io/v1/contacts' => Http::response(
                array_merge(
                    [
                        'id' => $uuid,
                        'resourceUri' => 'https://api.lexoffice.io/v1/contacts/' . $uuid
                    ]
                )
            ),
            'https://api.lexoffice.io/v1/contacts/'.$uuid => Http::response(
                array_merge(
                    ContactsEndpoint::generatePersonContactDataArray($customer),
                    [
                    'addresses' => [
                        'billing' => [
                            ContactsEndpoint::generateCustomerAddressDataArray(
                                streetAndNumber: $this->faker->address,
                                postcode: $this->faker->postcode,
                                city: $this->faker->city,
                                countryCode: $this->faker->countryCode,
                                supplement: $this->faker->address
                            )
                        ]
                    ]
                    ]
                )
            )
        ]);

        $customer->save();

        $this->assertModelExists($customer);

        $this->assertModelExists($customer->user);
    }

    public function testCreateCompanyCustomer()
    {
        $customer = Customer::factory()
            ->company()
            ->for(User::factory(), 'user')
            ->has($customerAddress = CustomerAddress::factory(), 'address')
            ->make();

        $customerContact = CustomerContact::factory()
            ->for($customer)->make();

        $uuid = $this->faker->uuid;

        Http::fake([
            'https://api.lexoffice.io/v1/contacts' => Http::response(
                array_merge(
                    [
                        'id' => $uuid,
                        'resourceUri' => 'https://api.lexoffice.io/v1/contacts/' . $uuid
                    ]
                )
            ),
            'https://api.lexoffice.io/v1/contacts/'.$uuid => Http::response(
                array_merge(
                    ContactsEndpoint::generatePersonContactDataArray($customer),
                    [
                    'addresses' => [
                        'billing' => [
                            ContactsEndpoint::generateCustomerAddressDataArray(
                                streetAndNumber: $this->faker->address,
                                postcode: $this->faker->postcode,
                                city: $this->faker->city,
                                countryCode: $this->faker->countryCode,
                                supplement: $this->faker->address
                            )
                        ]
                    ]
                    ]
                )
            )
        ]);

        $customer->save();

        $this->assertModelExists($customer);

        $this->assertModelExists($customer->user);
    }
}
