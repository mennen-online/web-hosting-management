<?php

namespace App\Services\Internetworx\Objects;

use App\Models\Domain;
use App\Services\Internetworx\Connector;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class DomainObject extends Connector
{
    public function index(int $page = 1, int $pageLimit = 250)
    {
        $this->prepareRequest();

        $response = $this->domrobot->call(
            'domain',
            'list',
            [
                'page' => $page,
                'pagelimit' => $pageLimit
            ]
        );

        return $this->processResponse($response, 'domain');
    }

    public function get(Domain $domain)
    {
        $this->prepareRequest();

        $response = $this->domrobot->call(
            'domain',
            'info',
            [
                'domain' => $domain->name
            ]
        );

        return $response['resData'];
    }

    public function check(string $domain)
    {
        $this->prepareRequest();

        Log::info('Start Domaincheck for ' . $domain);
        $response = $this->domrobot->call(
            'domain',
            'check',
            [
                'domain' => $domain
            ]
        );
        return $this->processResponse($response, 'domain');
    }

    public function getPrice(string $domain)
    {
        $this->prepareRequest();

        $response = $this->domrobot->call(
            'domain',
            'getdomainprice',
            [
                'domain' => $domain,
                'pricetype' => 'reg',
                'period' => '1Y'
            ]
        );

        return $this->processResponse($response, 'domain');
    }

    public function setDefaultNameserver(Domain $domain)
    {
        $this->prepareRequest();

        $response = $this->domrobot->call(
            'domain',
            'update',
            [
                'domain' => $domain->name,
                'ns' => [
                    'ns.inwx.de',
                    'ns2.inwx.de',
                    'ns3.inwx.eu'
                ],
            ]
        );

        Log::info(json_encode($response));
    }

    public function indexPrice(?string $tld = null)
    {
        $this->prepareRequest();

        $params = [
            'vatCC' => 'DE'
        ];

        if ($tld) {
            $params['tld'] = $tld;
        }

        $response = $this->domrobot->call('domain', 'getPrices', $params);

        return $this->processResponse($response, 'price');
    }

    public function create(Domain $domain)
    {
        $this->prepareRequest();

        $customer = $domain->customerProduct->customer;
        $contact = app()->make(ContactObject::class)->searchBy('email', $customer->user->email)->first();
        if ($contact === null) {
            $contact = app()->make(ContactObject::class)->create($customer->contacts()->first());
        }
        $domainResource = $this->domrobot->call(
            'domain',
            'create',
            [
                'domain' => $domain->name,
                'registrant' => $contact instanceof Collection ? $contact['roId'] : $contact,
                'admin' => $contact instanceof Collection ? $contact['roId'] : $contact,
                'tech' => config('internetworx.default_handle_id', 1211958),
                'billing' => config('internetworx.default_handle_id', 1211958)
            ]
        );

        Log::info(json_encode($domainResource));

        if (!Arr::has($domainResource, 'resData') && Arr::get($domainResource, 'code') === 2302) {
            return $this->get($domain);
        }

        return $domainResource['resData'];
    }

    public function delete(Domain|string $domain)
    {
        $this->prepareRequest();

        $response = $this->domrobot->call(
            'domain',
            'delete',
            [
                'domain' => $domain
            ]
        );

        return $this->processResponse($response, 'domain');
    }

    public function runOut(Domain $domain)
    {
        $this->prepareRequest();

        $response = $this->domrobot->call(
            'domain',
            'update',
            [
                'renewalMode' => 'AUTODELETE'
            ]
        );

        return $this->processResponse($response, 'domain');
    }

    public function renew(Domain $domain)
    {
        $this->prepareRequest();

        $response = $this->domrobot->call(
            'domain',
            'update',
            [
                'renewalMode' => 'AUTORENEW'
            ]
        );

        return $this->processResponse($response, 'domain');
    }
}
