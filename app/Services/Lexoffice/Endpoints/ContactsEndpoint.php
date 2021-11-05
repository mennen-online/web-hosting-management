<?php

namespace App\Services\Lexoffice\Endpoints;

use App\Models\Customer;
use App\Models\CustomerContact;
use App\Services\Lexoffice\Connector;
use Illuminate\Support\Str;
use stdClass;

class ContactsEndpoint extends Connector
{
    public function __construct(
        protected ?string $lexofficeAccessToken = null,
        protected ?string $filterEmail = null,
        protected ?string $filterName = null,
        protected ?int $filterNumber = null,
        protected bool $filterCustomer = false,
        protected bool $filterVendor = false
    ) {
        parent::__construct();
    }

    public function filterEmail(string $email): static {
        $this->filterEmail = $email;

        return $this;
    }

    public function filterName(string $name): static {
        $this->filterName = $name;

        return $this;
    }

    public function filterNumber(int $number): static {
        $this->filterNumber = $number;

        return $this;
    }

    public function onlyCustomer(): static {
        $this->filterCustomer = true;

        return $this;
    }

    public function onlyVendor(): static {
        $this->filterVendor = true;

        return $this;
    }

    public function index() {
        $query = [];

        if($this->filterEmail) {
            $query['email'] = $this->filterEmail;
        }

        if($this->filterName) {
            $query['name'] = $this->filterName;
        }

        if($this->filterNumber) {
            $query['number'] = $this->filterNumber;
        }

        if($this->filterCustomer) {
            $query['customer'] = "true";
        }

        if($this->filterVendor) {
            $query['vendor'] = "true";
        }

        return $this->getRequest('/contacts', $query);
    }

    public function get(string $id) {
        return $this->getRequest('/contacts/' . $id);
    }

    public function createCompanyContact(Customer $customer) {
        $role = new stdClass();
        $role->customer = new stdClass();
        $data = [
            'version' => 0,
            'roles' => $role,
            'company' => [
                'name' => $customer->companyName,
                'allowTaxFreeInvoices' => $customer->allowTaxFreeInvoices ?? false,
                'taxNumber' => $customer->taxNumber,
                'vatRegistrationId' => Str::upper($customer->vatRegistrationId)
            ]
        ];

        return $this->postRequest('/contacts', $data);
    }

    public function createPersonContact(Customer $customer) {
        $role = new stdClass();
        $role->customer = new stdClass();
        $data = [
            'version' => 0,
            'roles' => $role,
            'person' => [
                'salutation' => $customer->salutation,
                'firstName' => $customer->firstName,
                'lastName' => $customer->lastName,
            ],
            'note' => $customer->note
        ];

        return $this->postRequest('/contacts', $data);
    }

    public function createOrUpdateCompanyContactPerson(Customer $customer, CustomerContact $customerContact) {
        $data = [
            'company' => [
                'contactPersons' => [
                    'salutation' => $customerContact->salutation,
                    'firstName' => $customerContact->firstName,
                    'lastName' => $customerContact->lastName,
                    'primary' => true,
                    'emailAddress' => $customerContact->email,
                    'phoneNumber' => $customerContact->phone
                ]
            ]
        ];
        return $this->putRequest('/contacts', $customer->lexoffice_id, $data);
    }

    public function createOrUpdateCompanyBillingAddress(Customer $customer, string $supplement, string $streetAndNumber, string $postcode, string $city, string $countryCode) {
        $originalData = $this->get($customer->lexoffice_id);

        if(!property_exists($originalData, 'addresses')) {
            $originalData->addresses = new stdClass();
        }

        if(!property_exists($originalData->addresses, 'billing')) {
            $originalData->addresses->billing = [];
        }

        $originalData->addresses->billing[0] = [
            'supplement' => $supplement,
            'street' => $streetAndNumber,
            'zip' => $postcode,
            'city' => $city,
            'countryCode' => $countryCode
        ];

        return $this->putRequest('/contacts', $customer->lexoffice_id, (array)$originalData);
    }
}
