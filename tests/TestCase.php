<?php

namespace Tests;

use App\Services\Internetworx\Objects\ContactObject;
use App\Services\Internetworx\Objects\DomainObject;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseMigrations, WithFaker;

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

        Http::fake([
            'https://forge.laravel.com/api/v1/servers/' => Http::response([
                "server" => [
                    "id"            => $this->faker->randomNumber(),
                    "type"          => "app",
                    "provider"      => "hetzner",
                    "name"          => "q3efXImJDefRcXU0",
                    "size"          => "1",
                    "credential_id" => 79436,
                    "php_version"   => "php74",
                    "region"        => "2",
                    "database_type" => "mysql8",
                ],
            ])
        ]);
    }
}
