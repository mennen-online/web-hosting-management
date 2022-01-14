<?php

namespace App\Services\Forge\Endpoints;

use App\Models\Domain;
use App\Models\Server;
use App\Services\Forge\Connector;

class SitesEndpoint extends Connector
{
    protected const SITE_TYPE_PHP = 'php';

    protected const SITE_TYPE_HTML = 'html';

    protected const SITE_TYPE_SYMFONY = 'symfony';

    protected const SITE_TYPE_SYMFONY_DEV = 'symfony_dev';

    protected const SITE_TYPE_SYMFONY_FOUR = 'symfony_four';

    public function index(Server $server)
    {
        return $this->getRequest('/servers/' . $server->forge_id . '/sites');
    }

    public function get(Server $server, int $siteId)
    {
        return $this->getRequest('/servers/' . $server->forge_id . '/sites/' . $siteId);
    }

    public function create(Server $server, Domain $domain, array $params)
    {
        $data = array_merge(
            $params,
            [
                'domain' => $domain->name
            ]
        );
        return $this->postRequest('/servers/' . $server->forge_id . '/sites', $data);
    }

    public function log(Server $server, int $siteId)
    {
        return $this->getRequest('/servers/' . $server->forge_id . '/sites/' . $siteId . '/logs');
    }
}
