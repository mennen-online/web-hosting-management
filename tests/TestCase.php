<?php

namespace Tests;

use App\Models\Customer;
use App\Services\Internetworx\Objects\ContactObject;
use App\Services\Internetworx\Objects\DomainObject;
use App\Services\Lexoffice\Endpoints\ContactsEndpoint;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
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
            'https://forge.laravel.com/api/v1/servers/'    => Http::response([
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
            ]),
            'https://forge.laravel.com/api/v1/credentials' => Http::response([
                'credentials' => [
                    [
                        'id'   => 79436,
                        'type' => 'hetzner',
                        'name' => $this->faker->safeEmail
                    ]
                ]
            ]),
            'https://forge.laravel.com/api/v1/regions'     => Http::response(
                (array)json_decode(file_get_contents(__DIR__.'/JSON/Forge/RegionsResponse.json'))
            ),
        ]);
    }

    protected function createHttpFakeResponseForLexofficeContact(string $uuid, Customer $customer)
    {
        Http::fake([
            'https://api.lexoffice.io/v1/contacts'          => Http::response(array_merge([
                        'id'          => $uuid,
                        'resourceUri' => 'https://api.lexoffice.io/v1/contacts/' . $uuid
                    ])),
            'https://api.lexoffice.io/v1/contacts/' . $uuid => Http::response(array_merge(
                ContactsEndpoint::generatePersonContactDataArray($customer),
                [
                        'addresses' => [
                            'billing' => [
                                ContactsEndpoint::generateCustomerAddressDataArray(
                                    streetAndNumber: $this->faker->address,
                                    postcode: $this->faker->postcode,
                                    city: $this->faker->city,
                                    countryCode: $this->faker->countryCode,
                                    supplement: $this->faker->address
                                )
                            ]
                        ]
                ]
            ))
        ]);
    }
}
