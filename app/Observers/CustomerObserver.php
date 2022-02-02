<?php

namespace App\Observers;

use App\Models\Customer;
use App\Services\Lexoffice\Endpoints\ContactsEndpoint;

class CustomerObserver
{
    public function creating(Customer $customer) {
        if ($customer->customer_type) {
            $customer->lexoffice_id = match ($customer->customer_type) {
                'company' => app()->make(ContactsEndpoint::class)->createCompanyContact($customer)->id,
                'person' => app()->make(ContactsEndpoint::class)->createPersonContact($customer)->id,
            };
        }

        if ($customer->customer_type === 'company') {
            $customer->company = [
                'allowTaxFreeInvoices' => $customer->allowTaxFreeInvoices,
                'name' => $customer->companyName,
                'taxNumber' => $customer->taxNumber,
                'vatRegistrationId' => $customer->vatRegistrationId
            ];
        }

        $fillableFields = $customer->getFillable();
        foreach ($customer->attributes as $attribute => $value) {
            if (!in_array($attribute, $fillableFields)) {
                unset($customer->attributes[$attribute]);
            }
        }
    }

    /**
     * Handle the Customer "created" event.
     *
     * @param \App\Models\Customer $customer
     * @return void
     */
    public function created(Customer $customer)
    {
    }


    public function updating(Customer $customer) {
        if (!empty($customer->street_number)
            && !empty($customer->postcode)
            && !empty($customer->city)
            && !empty($customer->countryCode)) {
            app()->make(ContactsEndpoint::class)->createOrUpdateCompanyBillingAddress(
                $customer,
                $customer->supplement ?? '',
                $customer->street_number,
                $customer->postcode,
                $customer->city,
                $customer->countryCode
            );
        }

        $fillableFields = $customer->getFillable();
        foreach ($customer->attributes as $attribute => $value) {
            if (!in_array($attribute, $fillableFields)) {
                unset($customer->attributes[$attribute]);
            }
        }
    }

    /**
     * Handle the Customer "updated" event.
     *
     * @param \App\Models\Customer $customer
     * @return void
     */
    public function updated(Customer $customer)
    {
        //
    }

    /**
     * Handle the Customer "deleted" event.
     *
     * @param \App\Models\Customer $customer
     * @return void
     */
    public function deleted(Customer $customer)
    {
        //
    }

    /**
     * Handle the Customer "restored" event.
     *
     * @param \App\Models\Customer $customer
     * @return void
     */
    public function restored(Customer $customer)
    {
        //
    }

    /**
     * Handle the Customer "force deleted" event.
     *
     * @param \App\Models\Customer $customer
     * @return void
     */
    public function forceDeleted(Customer $customer)
    {
        //
    }
}
