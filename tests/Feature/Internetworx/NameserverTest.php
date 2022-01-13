<?php

namespace Tests\Feature\Internetworx;

use App\Models\CustomerProduct;
use App\Models\Domain;
use App\Models\Server;
use App\Services\Forge\Endpoints\ServersEndpoint;
use App\Services\Internetworx\Objects\DomainObject;
use App\Services\Internetworx\Objects\NameserverObject;
use Illuminate\Support\Arr;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NameserverTest extends TestCase
{
    protected DomainObject $domainObject;

    protected NameserverObject $nameserverObject;

    protected function setUp(): void {
        parent::setUp();

        if(!$this->domainObject->isOte()) {
            $this->markTestSkipped('Internetworx is Not in OTE Mode');
        }

        $this->domainObject = app()->make(DomainObject::class);

        $this->nameserverObject = app()->make(NameserverObject::class);
    }

    public function testCreateNameserverEntry() {
        $this->markTestIncomplete('Need to Refactor');

        $index = $this->domainObject->index();

        $index = $index->filter(function($domain) {
            if($domain['status'] === 'OK') {
                return $domain;
            }
        });

        $domainObject = $index->random(1)->first();

        $domain = Domain::factory()->create([
            'name' => $domainObject['domain']
        ]);

        $server = Server::factory()->create();

        CustomerProduct::factory()->create([
            'domain_id' => $domain->id,
            'server_id' => $server->id
        ]);

        sleep(60);

        $this->domainObject->setDefaultNameserver($domain);

        $this->nameserverObject->create($domain, $server);

        $info = $this->domainObject->get($domain);

        $this->assertSame([
            'ns.inwx.de',
            'ns2.inwx.de',
            'ns3.inwx.eu'
        ], $info['ns']);

        $nsInfo = $this->nameserverObject->info($domain);

        $serverInformation = app()->make(ServersEndpoint::class)->get($server);

        collect($nsInfo['record'])->each(function($nsEntry) use($serverInformation) {
            if($nsEntry['type'] === 'A') {
                $this->assertSame($serverInformation->server->ip_address, $nsEntry['content']);
            }
        });
    }
}
