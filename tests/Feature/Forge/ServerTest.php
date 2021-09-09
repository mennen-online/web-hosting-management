<?php

namespace Tests\Feature\Forge;

use App\Services\Forge\Endpoints\ServersEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
    }
}
