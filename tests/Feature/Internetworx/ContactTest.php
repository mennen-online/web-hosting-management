<?php

namespace Tests\Feature\Internetworx;

use App\Services\Internetworx\Objects\ContactObject;
use Arr;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContactTest extends TestCase
{
    protected ContactObject $contactObject;

    protected function setUp(): void {
        parent::setUp();

        $this->app['config']->set('internetworx', [
            'username' => config('internetworx.username'),
            'password' => config('internetworx.password')
        ]);

        $this->contactObject = new ContactObject();
    }

    public function testIndexContacts() {
        $response = $this->contactObject->index(1, 500);

        $this->assertIsArray($response);

        $this->assertEquals(1000, $response['code']);

        $this->assertArrayHasKey('contact', $response['resData']);

        $contacts = collect($response['resData']['contact']);

        $this->assertEquals($contacts->count(), $response['resData']['count']);

        $contacts->each(function($contact) {
            foreach(['roId', 'id', 'type', 'name', 'street', 'city', 'pc', 'cc', 'voice', 'email', 'protection', 'verificationStatus'] as $key) {
                $this->assertArrayHasKey($key, $contact);
            }
        });
    }

    public function testReceiveSingleContact() {
        $contacts = $this->contactObject->index(1, 500);

        $contact = collect($contacts['resData']['contact'])->random(1)->first();

        $response = $this->contactObject->index(1, 500, $contact['roId']);

        $result = Arr::first($response['resData']['contact']);

        foreach(['roId', 'id', 'type', 'name', 'street', 'city', 'pc', 'cc', 'voice', 'email', 'protection', 'verificationStatus'] as $key) {
            $this->assertArrayHasKey($key, $result);
        }
    }
}
