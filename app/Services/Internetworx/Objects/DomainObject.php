<?php

namespace App\Services\Internetworx\Objects;

use App\Models\Domain;
use App\Services\Internetworx\Connector;
use Illuminate\Support\Facades\Log;
use INWX\Domrobot;

class DomainObject extends Connector
{
    public function index(int $page = 1, int $pageLimit = 250) {
        return $this->domrobot->call('domain', 'list', [
            'page' => $page,
            'pagelimit' => $pageLimit
        ]);
    }

    public function check(string $domain) {
        Log::info('Start Domaincheck for ' . $domain);
        return $this->domrobot->call('domain', 'check', [
            'domain' => $domain
        ]);
    }

    public function getPrice(string $domain) {
        return $this->domrobot->call('domain', 'getdomainprice', [
            'domain' => $domain,
            'pricetype' => 'reg',
            'period' => '1Y'
        ]);
    }

    public function indexPrice() {
        return $this->domrobot->call('domain', 'getPrices');
    }
}
