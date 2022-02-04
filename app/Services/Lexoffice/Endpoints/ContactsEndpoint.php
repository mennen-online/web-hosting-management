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

    public function filterEmail(string $email): static
    {
        $this->filterEmail = $email;

        return $this;
    }

    public function filterName(string $name): static
    {
        $this->filterName = $name;

        return $this;
    }

    public function filterNumber(int $number): static
    {
        $this->filterNumber = $number;

        return $this;
    }

    public function onlyCustomer(): static
    {
        $this->filterCustomer = true;

        return $this;
    }

    public function onlyVendor(): static
    {
        $this->filterVendor = true;

        return $this;
    }

    public function index()
    {
        $query = [];

        if ($this->filterEmail) {
            $query['email'] = $this->filterEmail;
        }

        if ($this->filterName) {
            $query['name'] = $this->filterName;
        }

        if ($this->filterNumber) {
            $query['number'] = $this->filterNumber;
        }

        if ($this->filterCustomer) {
            $query['customer'] = true;
            $query['vendor'] = false;
        }

        if ($this->filterVendor) {
            $query['vendor'] = true;
            $query['customer'] = false;
        }

        return $this->getRequest('/contacts', $query);
    }

    public function get(string $id)
    {
        return $this->getRequest('/contacts/' . $id);
    }

    public function createCompanyContact(Customer $customer)
    {
        return $this->postRequest('/contacts', self::generateCompanyContactDataArray($customer));
    }

    public static function generateCompanyContactDataArray(Customer $customer): array
    {
        $role = new stdClass();
        $role->customer = new stdClass();
        return [
            'version' => 0,
            'roles' => $role,
            'company' => [
                'name' => $customer->companyName,
                'allowTaxFreeInvoices' => $customer->allowTaxFreeInvoices ?? false,
                'taxNumber' => $customer->taxNumber,
                'vatRegistrationId' => Str::upper($customer->vatRegistrationId)
            ]
        ];
    }

    public function createPersonContact(Customer $customer)
    {
        return $this->postRequest('/contacts', self::generatePersonContactDataArray($customer));
    }

    public static function generatePersonContactDataArray(Customer $customer) : array
    {
        $role = new stdClass();
        $role->customer = new stdClass();
        return [
            'version' => 0,
            'roles' => $role,
            'person' => [
                'salutation' => $customer->salutation,
                'firstName' => $customer->first_name,
                'lastName' => $customer->last_name,
            ],
            'note' => $customer->note
        ];
    }

    public function createOrUpdateCompanyContactPerson(Customer $customer, CustomerContact $customerContact)
    {
        $originalData = $this->get($customer->lexoffice_id);

        if (!property_exists($originalData->company, 'contactPersons')) {
            $originalData->company->contactPersons = [];
        }

        $originalData->company->contactPersons = [
            self::generateContactPersonDataArray($customerContact)
        ];
        return $this->putRequest('/contacts', $customer->lexoffice_id, $originalData);
    }

    public static function generateContactPersonDataArray(CustomerContact $customerContact) : array
    {
        return [
            'salutation' => $customerContact->salutation,
            'firstName' => $customerContact->first_name,
            'lastName' => $customerContact->last_name,
            'primary' => true,
            'emailAddress' => $customerContact->email,
            'phoneNumber' => $customerContact->phone
        ];
    }

    public function createOrUpdateCompanyBillingAddress(
        Customer $customer,
        string $supplement,
        string $streetAndNumber,
        string $postcode,
        string $city,
        string $countryCode
    ): object {
        $originalData = $this->get($customer->lexoffice_id);

        if (!property_exists($originalData, 'addresses')) {
            $originalData->addresses = new stdClass();
        }

        if (!property_exists($originalData->addresses, 'billing')) {
            $originalData->addresses->billing = [];
        }

        $originalData->addresses->billing[0] = self::generateCustomerAddressDataArray(
            $streetAndNumber,
            $postcode,
            $city,
            $countryCode,
            $supplement
        );

        return $this->putRequest('/contacts', $customer->lexoffice_id, (array)$originalData);
    }

    public static function generateCustomerAddressDataArray(
        string $streetAndNumber,
        string $postcode,
        string $city,
        string $countryCode,
        ?string $supplement = null
    ) : array {
        return [
            'supplement' => $supplement ?? '',
            'street' => $streetAndNumber,
            'zip' => $postcode,
            'city' => $city,
            'countryCode' => $countryCode
        ];
    }
}
