<?php

namespace Tests\Feature\Forge;

use App\Services\Forge\Endpoints\CredentialsEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CredentialTest extends TestCase
{
    protected CredentialsEndpoint $credentialsEndpoint;

    protected function setUp(): void {
        parent::setUp();

        $this->app['config']->set('forge.token', env('FORGE_TOKEN'));

        $this->credentialsEndpoint = app()->make(CredentialsEndpoint::class);

        if($this->credentialsEndpoint->isForgeAvailable() === false) {
            $this->markTestSkipped('Forge is currently not Available');
        }
    }

    public function testIndexCredentials() {
        $response = $this->credentialsEndpoint->index();

        $this->assertObjectHasAttribute('credentials', $response);

        $this->assertIsArray($response->credentials);
    }
}
