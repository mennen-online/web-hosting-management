<?php

namespace App\Services\Forge\Endpoints;

use App\Models\Server;
use App\Services\Forge\Connector;

class ServersEndpoint extends Connector
{
    public function index() {
        return $this->getRequest('/servers')->object();
    }

    public function get(Server $server) {
        return $this->getRequest('/servers/' . $server->forge_id)->object();
    }

    public function events(?Server $server) {
        return $this->getRequest('/servers/events', $server ? [
            'server_id' => $server->forge_id
        ] : []);
    }
}
