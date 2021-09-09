<?php

namespace Tests\Feature\Internetworx;

use App\Services\Internetworx\Objects\DomainObject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use INWX\Domrobot;
use Tests\TestCase;

class DomainTest extends TestCase
{
    protected DomainObject $domainObject;

    protected function setUp(): void {
        parent::setUp();

        $this->app['config']->set('internetworx', [
            'username' => config('internetworx.username'),
            'password' => config('internetworx.password')
        ]);

        $this->domainObject = new DomainObject();
    }

    public function testCheckDomain() {
        $domain = Str::random().'.de';

        $response = $this->domainObject->check($domain);

        $this->assertIsArray($response);

        $this->assertEquals(1000, $response['code']);

        $this->assertArrayHasKey('domain', $response['resData']);

        foreach(['domain', 'avail', 'status', 'checktime', 'premium'] as $key) {
            $this->assertArrayHasKey($key, $response['resData']['domain'][0]);
        }
    }

    public function testIndexDomain() {
        $response = $this->domainObject->index(0, 5000);

        $this->assertIsArray($response);

        $this->assertEquals(1000, $response['code']);

        $this->assertArrayHasKey('domain', $response['resData']);

        $domains = collect($response['resData']['domain']);

        $this->assertEquals($domains->count(), $response['resData']['count']);

        $domains->each(function($domain) {
            foreach(['roId', 'domain', 'domain-ace', 'withPrivacy', 'period', 'crDate', 'exDate', 'reDate', 'upDate', 'transferLock', 'status', 'authCode', 'renewalMode', 'transferMode', 'registrant', 'admin', 'tech', 'billing', 'ns', 'verificationStatus'] as $key) {
                $this->assertArrayHasKey($key, $domain);
            }
        });
    }
}
