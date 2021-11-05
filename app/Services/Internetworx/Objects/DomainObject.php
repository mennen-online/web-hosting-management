<?php

namespace App\Services\Internetworx\Objects;

use App\Models\Domain;
use App\Services\Internetworx\Connector;
use Illuminate\Support\Facades\Log;
use INWX\Domrobot;

class DomainObject extends Connector
{
    public function index(int $page = 1, int $pageLimit = 250) {
        $response = $this->domrobot->call('domain', 'list', [
            'page' => $page,
            'pagelimit' => $pageLimit
        ]);

        return $this->processResponse($response, 'domain');
    }

    public function check(string $domain) {
        Log::info('Start Domaincheck for ' . $domain);
        $response =  $this->domrobot->call('domain', 'check', [
            'domain' => $domain
        ]);
        return $this->processResponse($response, 'domain');
    }

    public function getPrice(string $domain) {
        $response = $this->domrobot->call('domain', 'getdomainprice', [
            'domain' => $domain,
            'pricetype' => 'reg',
            'period' => '1Y'
        ]);

        return $this->processResponse($response, 'domain');
    }

    public function indexPrice() {
        $response = $this->domrobot->call('domain', 'getPrices');

        return $this->processResponse($response, 'price');
    }

    public function create(Domain $domain) {
        $customer = $domain->customerProduct->customer;
        $contact = app()->make(ContactObject::class)->searchBy('email', $customer->user->email)->first();
        $domainResource = $this->domrobot->call('domain', 'create', [
            'domain' => $domain->name,
            'registrant' => $contact['roId'],
            'admin' => $contact['roId'],
            'tech' => config('internetworx.default_handle_id'),
            'billing' => config('internetworx.default_handle_id')
        ]);
        $domain->update(['registrar_id' => $domainResource['roId']]);

        return $this->processResponse($http_response_header, 'domain');
    }

    public function delete(Domain|string $domain) {
        $response = $this->domrobot->call('domain', 'delete', [
            'domain' => $domain
        ]);

        return $this->processResponse($response, 'domain');
    }
}
