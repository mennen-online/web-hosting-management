<?php

namespace Tests\Feature\CustomerInvoice;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerContact;
use App\Models\CustomerProduct;
use App\Models\Product;
use App\Models\User;
use App\Services\Lexoffice\Endpoints\ContactsEndpoint;
use App\Services\Lexoffice\Endpoints\InvoicesEndpoint;
use App\Services\Lexoffice\Lexoffice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CreateCustomerInvoiceTest extends TestCase
{
    protected $user;

    protected $contact;

    protected function setUp(): void
    {
        parent::setUp();

        $customer = Customer::factory()->make();

        $this->createHttpFakeResponseForLexofficeContact($customer->lexoffice_id, $customer);

        if (!$this->user = User::whereHas('customer')->first()) {
            $this->user = User::factory()
                ->has(
                    Customer::factory()
                        ->has($this->contact = CustomerContact::factory(), 'contacts')
                        ->has($address = CustomerAddress::factory(), 'address')
                )->create();
        }
    }

    public function testCreateInvoice()
    {
        $invoicesEndpoint = app()->make(InvoicesEndpoint::class);

        $customerProduct = CustomerProduct::factory()
            ->for($this->user->customer)
            ->for(Product::factory())
            ->create();

        $this->assertModelExists($customerProduct);

        Http::fake([
            'https://api.lexoffice.io/v1/invoices?finalize=true' => Http::response([
                'voucherDate' => Lexoffice::buildLexofficeDate(now()),
                'address' => $invoicesEndpoint->buildAddress($customerProduct),
                'lineItems' => $invoicesEndpoint->buildLineItems($customerProduct),
                'totalPrice' => $invoicesEndpoint->buildTotalPrice(),
                'taxConditions' => $invoicesEndpoint->buildTaxConditions(),
                'shippingConditions' => $invoicesEndpoint->buildShippingConditions()
            ])
        ]);

        $invoicesEndpoint->create($customerProduct);
    }
}
