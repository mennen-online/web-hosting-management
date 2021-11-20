<?php

namespace Tests\Feature\Lexoffice;

use App\Models\Customer;
use App\Models\Domain;
use App\Models\Product;
use App\Models\User;
use App\Services\Lexoffice\Endpoints\ContactsEndpoint;
use App\Services\Lexoffice\Endpoints\InvoicesEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use WithFaker;

    protected Product $product;

    protected Domain $domain;

    protected Customer $customer;

    protected function setUp(): void {
        parent::setUp();

        Artisan::call('internetworx:domains:price:sync');

        $this->product = Product::factory()->create([
                'name'        => 'WordPress',
                'description' => 'Simple WordPress',
                'price'       => 120
            ]);


        $contactEndpoint = app()->make(ContactsEndpoint::class);

        $contacts = $contactEndpoint->index();

        if ($contacts->count() === 0 || Customer::where('lexoffice_id', $contacts->first()->id)->first() === null) {
            if (!$user = User::first()) {
                $user = User::factory();
            }

            $customer = Customer::factory()->for($user)->create([
                'customer_type' => 'person',
                'salutation'    => Arr::random(['Herr', 'Frau', '']),
                'firstName'     => $this->faker->firstName,
                'lastName'      => $this->faker->lastName,
                'note'          => ''
            ]);

            $customerProduct = $customer->products()->create([
                'product_id' => $this->product->id,
            ]);

            $this->domain = Domain::factory()->create(
                    [
                        'user_id' => $user->id,
                        'name'    => $this->faker->word.'.de'
                    ]
                );

            $customerProduct->update(['domain_id' => $this->domain->id]);

            $customer->supplement = '';
            $customer->street_number = $this->faker->streetAddress;
            $customer->postcode = $this->faker->postcode;
            $customer->city = $this->faker->city;
            $customer->countryCode = $this->faker->countryCode;

            app()->make(ContactsEndpoint::class)->createOrUpdateCompanyBillingAddress($customer, $customer->supplement ?? '', $customer->street_number, $customer->postcode, $customer->city, $customer->countryCode);

            $this->customer = $customer;
        }
    }

    public function testCreateInvoice() {
        $invoiceEndpoint = app()->make(InvoicesEndpoint::class);

        $result = $invoiceEndpoint->create($this->customer->products()->first());

        $this->assertObjectHasAttribute('id', $result);
    }


}
