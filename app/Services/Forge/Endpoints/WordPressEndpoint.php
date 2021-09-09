<?php

namespace App\Services\Forge\Endpoints;

use App\Models\Server;
use App\Services\Forge\Connector;

class WordPressEndpoint extends Connector
{
    public function install(Server $server, int $siteId) {
        return $this->postRequest('/servers/' . $server->forge_id . '/sites/' . $siteId . '/wordpress', null);
    }

    public function uninstall(Server $server, int $siteId) {
        return $this->deleteRequest('/servers/' . $server->force_id . '/sites/' . $siteId . '/wordpress', null);
    }
}
