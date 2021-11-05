<?php

namespace App\Services\Internetworx\Objects;

use App\Models\Customer;
use App\Models\CustomerContact;
use App\Services\Internetworx\Connector;
use App\Services\Lexoffice\Endpoints\ContactsEndpoint;
use Illuminate\Support\Arr;

class ContactObject extends Connector
{
    public function index(int $page = 1, int $pageLimit = 250, ?int $id = null) {
        $params = [
            'page' => $page,
            'pagelimit' => $pageLimit
        ];

        if($id) {
            $params['id'] = $id;
        }

        $response = $this->domrobot->call('contact', 'list', $params);

        return $this->processResponse($response, 'contact');
    }

    public function searchBy(string $field, string $value) {
        $contacts = $this->processResponse($this->index(1, 5000), 'contact');

        return collect($contacts)->filter(function($contact) use($field, $value) {
           if(Arr::has($contact, $field) && Arr::get($contact, $field) === $value) {
               return $contact;
           }
        });
    }

    public function create(CustomerContact $contact) {
        $params = [];

        $contactObject = app()->make(ContactsEndpoint::class)->get($contact->customer->lexoffice_id);

        $params['role'] = $contact->customer->company ? 'ORG' : 'PERSON';

        $params['name'] = $contact->first_name . ' ' . $contact->last_name;

        if($contact->customer->company) {
            $params['org'] = $contactObject->company->name;
        }

        if(property_exists($contactObject->addresses, 'business')) {
            $address = Arr::first($contactObject->addresses->business);
        }elseif(property_exists($contact->addresses, 'shipping')) {
            $address = Arr::first($contactObject->addresses->shipping);
        }

        $params['street'] = $address->street;
        $params['city'] = $address->city;
        $params['pc'] = $address->zip;
        $params['cc'] = $address->countryCode;

        if(property_exists($contactObject->phoneNumbers, 'business')) {
            $params['voice'] = Arr::first($contactObject->phoneNumbers->business);
        }elseif(property_exists($contactObject->phoneNumbers, 'office')) {
            $params['voice'] = Arr::first($contactObject->phoneNumbers->office);
        }elseif(property_exists($contactObject->phoneNumbers, 'mobile')) {
            $params['voice'] = Arr::first($contactObject->phoneNumbers->mobile);
        }elseif(property_exists($contactObject->phoneNumbers, 'private')) {
            $params['voice'] = Arr::first($contactObject->phoneNumbers->private);
        }elseif(property_exists($contactObject->phoneNumbers, 'fax')) {
            $params['voice'] = Arr::first($contactObject->phoneNumbers->fax);
        }elseif(property_exists($contactObject->phoneNumbers, 'other')) {
            $params['voice'] = Arr::first($contactObject->phoneNumbers->other);
        }

        $params['email'] = $contact->email;

        $response = $this->domrobot->call('contact', 'create', $params);

        return $this->processResponse($response, 'contact');
    }

    public function delete(Customer|int $customer) {
        if($customer instanceof Customer) {
            $response = $this->domrobot->call('contact', 'delete', [
                'id' => $customer->user->id
            ]);

        }

        if(is_int($customer)) {
            $response = $this->domrobot->call('contact', 'delete', [
                'id' => $customer
            ]);
        }

        return $this->processResponse($response, 'contact');
    }
}
