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

        if(!$this->domainObject->isOte()) {
            $this->markTestSkipped('Internetworx is Not in OTE Mode');
        }
    }

    public function testCheckDomain() {
        $domain = Str::random().'.de';

        $response = $this->domainObject->check($domain);

        foreach(['domain', 'avail', 'status', 'checktime', 'premium'] as $key) {
            $this->assertArrayHasKey($key, $response[0]);
        }
    }

    public function testIndexDomain() {
        $domains = $this->domainObject->index(0, 5000);

        $domains->each(function($domain) {
            foreach(['roId', 'domain', 'domain-ace', 'withPrivacy', 'period', 'crDate', 'exDate', 'reDate', 'upDate', 'transferLock', 'status', 'authCode', 'renewalMode', 'transferMode', 'registrant', 'admin', 'tech', 'billing', 'ns', 'verificationStatus'] as $key) {
                $this->assertArrayHasKey($key, $domain);
            }
        });
    }
}
