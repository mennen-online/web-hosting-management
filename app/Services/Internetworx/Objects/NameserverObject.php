<?php

namespace App\Services\Internetworx\Objects;

use App\Models\Domain;
use App\Models\Server;
use App\Services\Forge\Endpoints\ServersEndpoint;
use App\Services\Internetworx\Connector;
use Illuminate\Support\Facades\Log;

class NameserverObject extends Connector
{
    public function info(Domain $domain) {
        $response = $this->domrobot->call('nameserver', 'info', [
            'domain' => $domain->name
        ]);

        if($response['code'] === 2303) {
            return $this->create($domain, $domain->customerProduct->server);
        }

        return $response['resData'];
    }

    public function create(Domain $domain, Server $server) {
        $serverInformation = app()->make(ServersEndpoint::class)->get($server);
        $response = $this->domrobot->call('nameserver', 'create', [
            'domain' => $domain->name,
            'type' => 'MASTER',
            'ns' => [
                'ns.inwx.de',
                'ns2.inwx.de',
                'ns3.inwx.eu'
            ],
            'web' => $serverInformation->server->ip_address
        ]);
        Log::info('Nameserver Creation for Domain ' . $domain->name .' - Response:');
        Log::info(json_encode($response));

        return $response;
    }
}