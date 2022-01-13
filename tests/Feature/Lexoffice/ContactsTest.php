<?php

namespace Tests\Feature\Lexoffice;

use App\Models\Customer;
use App\Services\Lexoffice\Endpoints\ContactsEndpoint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class ContactsTest extends TestCase
{
    use WithFaker;

    protected ContactsEndpoint $contactsEndpoint;

    protected function setUp(): void {
        parent::setUp();

        if (config('lexoffice.access_token') === null) {
            $this->markTestSkipped('No Lexoffice Access Token provided for Tests');
        }

        $this->contactsEndpoint = app()->make(ContactsEndpoint::class);
    }

    public function testLexofficePersonContactCreation() {
        $this->markTestSkipped('Need to Refactor');
        $customer = new Customer;
        $customer->salutation = Arr::random(['Frau', 'Herr']);
        $customer->firstName = $this->faker->firstName;
        $customer->lastName = $this->faker->lastName;

        $result = $this->contactsEndpoint->createPersonContact($customer);

        $this->assertObjectHasAttribute('id', $result);
    }

    public function testLexofficeCompanyContactCreation() {
        $customer = new Customer();
        $customer->companyName = $this->faker->company;
        $customer->allowTaxFreeInvoices = $this->faker->boolean;
        $customer->taxNumber = Str::random();
        $customer->vatRegistrationId = 'DE123456789';
        $result = $this->contactsEndpoint->createCompanyContact($customer);

        $this->assertObjectHasAttribute('id', $result);
    }

    public function testLexofficeIndexWithoutFilter() {
        $result = $this->contactsEndpoint->setPageSize(250)->index();

        collect($result)->each(function ($contact) {
            $this->assertObjectHasAttribute('id', $contact);

            $this->assertObjectHasAttribute('organizationId', $contact);
        });
    }

    public function testLexofficeGetSingleContact() {
        $contacts = $this->contactsEndpoint->index();

        $contact = collect($contacts)->random(1)->first();

        $result = $this->contactsEndpoint->get($contact->id);

        $this->assertEquals($result->id, $contact->id);
    }
}
