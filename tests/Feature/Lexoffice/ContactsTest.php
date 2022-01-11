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

        collect($result->content)->each(function ($contact) {
            $this->assertObjectHasAttribute('id', $contact);

            $this->assertObjectHasAttribute('organizationId', $contact);
        });
    }

    public function testLexofficeGetSingleContact() {
        $contacts = $this->contactsEndpoint->index();

        $contact = collect($contacts->content)->random(1)->first();

        $result = $this->contactsEndpoint->get($contact->id);

        $this->assertEquals($result->id, $contact->id);
    }

    public function testLexofficeCompanyImport() {
        $json = '{
    "id": "be9475f4-ef80-442b-8ab9-3ab8b1a2aeb9",
    "organizationId": "aa93e8a8-2aa3-470b-b914-caad8a255dd8",
    "version": 1,
    "roles": {
        "customer": {
            "number": 10307
        },
        "vendor": {
            "number": 70303
        }
    },
    "company": {
        "name": "Testfirma",
        "taxNumber": "12345/12345",
        "vatRegistrationId": "DE123456789",
        "allowTaxFreeInvoices": true,
        "contactPersons": [
            {
                "salutation": "Herr",
                "firstName": "Max",
                "lastName": "Mustermann",
                "primary": true,
                "emailAddress": "contactpersonmail@lexoffice.de",
                "phoneNumber": "08000/11111"
            }
        ]
    },
    "addresses": {
        "billing": [
            {
                "supplement": "Rechnungsadressenzusatz",
                "street": "Hauptstr. 5",
                "zip": "12345",
                "city": "Musterort",
                "countryCode": "DE"
            }
        ],
        "shipping": [
            {
                "supplement": "Lieferadressenzusatz",
                "street": "Schulstr. 13",
                "zip": "76543",
                "city": "MUsterstadt",
                "countryCode": "DE"
            }
        ]
    },
    "xRechnung": {
        "buyerReference": "04011000-1234512345-35",
        "vendorNumberAtCustomer": "70123456"
    },
    "emailAddresses": {
        "business": [
            "business@lexoffice.de"
        ],
        "office": [
            "office@lexoffice.de"
        ],
        "private": [
            "private@lexoffice.de"
        ],
        "other": [
            "other@lexoffice.de"
        ]
    },
    "phoneNumbers": {
        "business": [
            "08000/1231"
        ],
        "office": [
            "08000/1232"
        ],
        "mobile": [
            "08000/1233"
        ],
        "private": [
            "08000/1234"
        ],
        "fax": [
            "08000/1235"
        ],
        "other": [
            "08000/1236"
        ]
    },
    "note": "Notizen",
    "archived": false
}';
        $array = json_decode($json);

        Http::fake([
            'https://api.lexoffice.io/v1/contacts?page=0&size=25' => Http::response([
                'content' => [$array]
            ])
        ]);

        $this->contactsEndpoint->index()->each(function($customer) {
            $customerModel = new Customer();

            $customerModel->lexoffice_id = $customer->id;

            $customerModel->company = $customer->company;

            $customerModel->save();

            $this->assertModelExists($customerModel);

            collect($customer?->company?->contactPersons)->each(function($contactPerson) use($customerModel) {
                $data = (array)$contactPerson;

                if($customerModel->contact->count() === 0) {
                    $data['primary'] = true;
                }

                $contact = $customerModel->contact()->create($data);

                $this->assertModelExists($contact);
            });
        });
    }
}
