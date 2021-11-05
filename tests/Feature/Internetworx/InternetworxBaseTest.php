<?php

namespace Tests\Feature\Internetworx;

use App\Services\Internetworx\Connector;
use Tests\TestCase;

class InternetworxBaseTest extends TestCase
{
    protected function setUp(): void {
        parent::setUp();

        $this->app['config']->set('internetworx', [
            'username' => config('internetworx.username'),
            'password' => config('internetworx.password')
        ]);

        if(!app()->make(Connector::class)->isOte()) {
            $this->markTestSkipped('INWX Tests cannot performed on Live');
        }
    }

    public function testInternetworxIsOnOte() {
        $this->assertTrue(app()->make(Connector::class)->isOte());
    }
}
