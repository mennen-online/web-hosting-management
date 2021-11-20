<?php

namespace App\Observers;

use App\Models\CustomerContact;
use App\Services\Internetworx\Objects\ContactObject;
use App\Services\Lexoffice\Endpoints\ContactsEndpoint;

class CustomerContactObserver
{
    /**
     * Handle the CustomerContact "created" event.
     *
     * @param  \App\Models\CustomerContact  $customerContact
     * @return void
     */
    public function created(CustomerContact $customerContact)
    {
        app()->make(ContactObject::class)->create($customerContact);

        if($customerContact->customer->contacts()->count() === 1) {
            $customer = $customerContact->customer;
            $contactsEndpoint = app()->make(ContactsEndpoint::class);

            $contact = $contactsEndpoint->get($customer->lexoffice_id);

            if(property_exists($contact, 'company')) {
                app()->make(ContactsEndpoint::class)->createOrUpdateCompanyContactPerson($customer, $customerContact);
            }
        }


    }

    /**
     * Handle the CustomerContact "updated" event.
     *
     * @param  \App\Models\CustomerContact  $customerContact
     * @return void
     */
    public function updated(CustomerContact $customerContact)
    {
        //
    }

    /**
     * Handle the CustomerContact "deleted" event.
     *
     * @param  \App\Models\CustomerContact  $customerContact
     * @return void
     */
    public function deleted(CustomerContact $customerContact)
    {
        //
    }

    /**
     * Handle the CustomerContact "restored" event.
     *
     * @param  \App\Models\CustomerContact  $customerContact
     * @return void
     */
    public function restored(CustomerContact $customerContact)
    {
        //
    }

    /**
     * Handle the CustomerContact "force deleted" event.
     *
     * @param  \App\Models\CustomerContact  $customerContact
     * @return void
     */
    public function forceDeleted(CustomerContact $customerContact)
    {
        //
    }
}
