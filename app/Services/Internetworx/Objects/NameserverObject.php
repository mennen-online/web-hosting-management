<?php

namespace App\Services\Internetworx\Objects;

use App\Models\Domain;
use App\Models\Server;
use App\Services\Forge\Endpoints\ServersEndpoint;
use App\Services\Internetworx\Connector;

class NameserverObject extends Connector
{
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
            'masterIp' => $serverInformation->server->ip_address
        ]);

        return $this->processResponse($response, 'nameserver');
    }
}