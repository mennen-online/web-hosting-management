<?php

namespace Tests;

use App\Services\Internetworx\Objects\ContactObject;
use App\Services\Internetworx\Objects\DomainObject;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseMigrations;

    protected DomainObject $domainObject;

    protected ContactObject $contactObject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            DatabaseSeeder::class
        ]);

        $this->app['config']->set('app.url', ['administration.test']);

        $this->app['config']->set('lexoffice.access_token', env('LEXOFFICE_ACCESS_TOKEN'));

        $this->domainObject = app()->make(DomainObject::class);

        $this->contactObject = app()->make(ContactObject::class);
    }

    /*protected function tearDown(): void {
        parent::tearDown();

        $page = 1;

        $pageSize = 500;

        if($this->domainObject->isOte()) {
            $results = $this->domainObject->index($page, $pageSize);
            $results->each(function ($domain) {
                if ($domain['status'] !== 'DELETE REQUESTED') {
                    $this->domainObject->delete($domain['domain']);
                    echo "Deleted ".$domain['domain']."\r\n";
                }
            });
        }

        if($this->contactObject->isOte()) {
            $this->contactObject->index(1, 50000)->each(function ($contact) {
                $this->contactObject->delete($contact['id']);
            });
        }
    }*/
}
