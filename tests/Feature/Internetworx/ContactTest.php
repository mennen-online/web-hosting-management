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

        if(!$this->contactObject->isOte()) {
            $this->markTestSkipped('Internetworx is Not in OTE Mode');
        }
    }

    public function testIndexContacts() {
        $contacts = $this->contactObject->index(1, 500);

        $contacts->each(function($contact) {
            foreach(['roId', 'id', 'type', 'name', 'street', 'city', 'pc', 'cc', 'voice', 'email', 'protection', 'verificationStatus'] as $key) {
                $this->assertArrayHasKey($key, $contact);
            }
        });
    }

    public function testReceiveSingleContact() {
        $contacts = $this->contactObject->index(1, 500);

        $result = $contacts->first();

        foreach(['roId', 'id', 'type', 'name', 'street', 'city', 'pc', 'cc', 'voice', 'email', 'protection', 'verificationStatus'] as $key) {
            $this->assertArrayHasKey($key, $result);
        }
    }
}
