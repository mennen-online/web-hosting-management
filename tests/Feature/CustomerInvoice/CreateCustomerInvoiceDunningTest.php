<?php

namespace Tests\Feature\CustomerInvoice;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerContact;
use App\Models\CustomerInvoice;
use App\Models\CustomerProduct;
use App\Models\Product;
use App\Models\User;
use App\Services\Lexoffice\Endpoints\ContactsEndpoint;
use App\Services\Lexoffice\Endpoints\DunningEndpoint;
use App\Services\Lexoffice\Endpoints\InvoicesEndpoint;
use App\Services\Lexoffice\Lexoffice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CreateCustomerInvoiceDunningTest extends TestCase
{
    protected $user;

    protected $contact;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('lexoffice:contacts:sync');

        if(!$this->user = User::whereHas('customer')->first()) {
            $this->user = User::factory()
                ->has(
                    Customer::factory()
                        ->has($this->contact = CustomerContact::factory(), 'contacts')
                        ->has($address = CustomerAddress::factory(), 'address')
                )->create();

            $contact = app()->make(ContactsEndpoint::class)->createOrUpdateCompanyBillingAddress(
                $this->user->customer,
                $this->user->customer->address->supplement,
                $this->user->customer->address->street,
                $this->user->customer->address->zip,
                $this->user->customer->address->city,
                $this->user->customer->address->country_code
            );

            $this->user->customer->contacts->first()->update(['lexoffice_id' => $contact->id]);
        }

        $customerProduct = CustomerProduct::factory()
            ->for($this->user->customer)
            ->for(Product::factory())
            ->create();

        $this->assertModelExists($customerProduct);

        Lexoffice::storeCustomerInvoice(
            app()->make(InvoicesEndpoint::class)->get(
                new CustomerInvoice(['lexoffice_id' => app()->make(InvoicesEndpoint::class)
                    ->create($customerProduct)->id])
            ),
            $customerProduct->customer
        );
    }

    public function testCreateInvoiceDunning() {
        $result = app()->make(DunningEndpoint::class)->create($this->user->customer->invoices->first());

        dd($result);
    }
}
