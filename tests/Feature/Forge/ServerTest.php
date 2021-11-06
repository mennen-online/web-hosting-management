<?php

namespace Tests\Feature\Forge;

use App\Models\Server;
use App\Services\Forge\Endpoints\CredentialsEndpoint;
use App\Services\Forge\Endpoints\RegionsEndpoint;
use App\Services\Forge\Endpoints\ServersEndpoint;
use Arr;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class ServerTest extends TestCase
{
    protected ServersEndpoint $serversEndpoint;

    protected function setUp(): void {
        parent::setUp();

        $this->app['config']->set('forge.token', env('FORGE_TOKEN'));

        $this->serversEndpoint = app()->make(ServersEndpoint::class);
    }

    public function testIndexServers() {
        $response = $this->serversEndpoint->index();

        $this->assertObjectHasAttribute('servers', $response);

        $this->assertIsArray($response->servers);

        collect($response->servers)->each(function($server) {
            $this->assertObjectHasAttribute('id', $server);

            $this->assertObjectHasAttribute('credential_id', $server);

            $this->assertObjectHasAttribute('name', $server);

            $this->assertObjectHasAttribute('size', $server);

            $this->assertObjectHasAttribute('region', $server);

            $this->assertObjectHasAttribute('php_version', $server);

            $this->assertObjectHasAttribute('database_type', $server);

            $this->assertObjectHasAttribute('ip_address', $server);

            $this->assertObjectHasAttribute('private_ip_address', $server);

            $this->assertObjectHasAttribute('blackfire_status', $server);

            $this->assertObjectHasAttribute('papertrail_status', $server);

            $this->assertObjectHasAttribute('revoked', $server);

            $this->assertObjectHasAttribute('created_at', $server);

            $this->assertObjectHasAttribute('is_ready', $server);

            $this->assertObjectHasAttribute('network', $server);
        });
    }

    public function testCreateServer() {
        $credentials = collect(app()->make(CredentialsEndpoint::class)->index()->credentials);
        $regions = collect(app()->make(RegionsEndpoint::class)->index()->regions->hetzner);
        $region = $regions->random(1)->first();
        $size = collect($region->sizes)->first();
        $response = $this->serversEndpoint->create(
            [
                'type' => 'app',
                'name' => Str::random(),
                'size' => $size->id,
                'provider' => 'hetzner',
                'credential_id' => $credentials->first()->id,
                'php_version' => 'php74',
                'region' => $region->id,
                'database_type' => 'mysql8',
            ]
        );

        $this->assertObjectHasAttribute('server', $response);

        $this->assertObjectHasAttribute('sudo_password', $response);

        $this->assertObjectHasAttribute('database_password', $response);

        $server = $response->server;

        $this->assertObjectHasAttribute('id', $server);

        $this->assertObjectHasAttribute('credential_id', $server);

        $this->assertObjectHasAttribute('name', $server);

        $this->assertObjectHasAttribute('size', $server);

        $this->assertObjectHasAttribute('region', $server);

        $this->assertObjectHasAttribute('php_version', $server);

        $this->assertObjectHasAttribute('database_type', $server);

        $this->assertObjectHasAttribute('ip_address', $server);

        $this->assertObjectHasAttribute('private_ip_address', $server);

        $this->assertObjectHasAttribute('blackfire_status', $server);

        $this->assertObjectHasAttribute('papertrail_status', $server);

        $this->assertObjectHasAttribute('revoked', $server);

        $this->assertObjectHasAttribute('created_at', $server);

        $this->assertObjectHasAttribute('is_ready', $server);

        $this->assertObjectHasAttribute('network', $server);

        $this->assertFalse($server->is_ready);

        $forgeServer = Server::create([
            'name' => $server->name,
            'forge_id' => $server->id
        ]);

        $this->assertModelExists($forgeServer);

        $this->serversEndpoint->delete($forgeServer);

        $forgeServer->delete();

        $this->assertModelMissing($forgeServer);
    }
}
