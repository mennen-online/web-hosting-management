<?php

namespace App\Services\Forge\Endpoints;

use App\Models\Server;
use App\Services\Forge\Connector;
use Illuminate\Support\Str;

class ServersEndpoint extends Connector
{
    public function index() {
        return $this->getRequest('/servers')->object();
    }

    public function get(Server $server) {
        return $this->getRequest('/servers/' . $server->forge_id)->object();
    }

    public function create(array $params = [
        'type' => 'app',
        'provider' => 'hetzner'
    ]) {
        $credentials = collect(app()->make(CredentialsEndpoint::class)->index()->credentials);
        $regions = collect(app()->make(RegionsEndpoint::class)->index()->regions->hetzner);
        $region = $regions->random(1)->first();
        $size = collect($region->sizes)->first();
        $params['name'] = Str::random();
        $params['size'] = $size->id;
        $params['credential_id'] = $credentials->first()->id;
        $params['php_version'] = 'php74';
        $params['region'] = $region->id;
        $params['database_type'] = 'mysql8';
        return $this->postRequest('/servers/', $params)->object();
    }

    public function delete(Server $server) {
        return $this->deleteRequest('/servers/' . $server->forge_id)->successful();
    }

    public function events(?Server $server) {
        return $this->getRequest('/servers/events', $server ? [
            'server_id' => $server->forge_id
        ] : []);
    }
}
