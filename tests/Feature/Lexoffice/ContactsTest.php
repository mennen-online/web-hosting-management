<?php

namespace Tests\Feature\Lexoffice;

use App\Services\Lexoffice\Endpoints\ContactsEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContactsTest extends TestCase
{
    protected ContactsEndpoint $contactsEndpoint;

    protected function setUp(): void {
        parent::setUp();

        $this->app['config']->set('lexoffice.access_token', '0ff74fcd-bd92-4eb2-bcd6-1fc43d1768dc');

        if(config('lexoffice.access_token') === null) {
            $this->markTestSkipped('No Lexoffice Access Token provided for Tests');
        }

        $this->contactsEndpoint = app()->make(ContactsEndpoint::class);
    }

    public function testLexofficeIndexWithoutFilter() {
        $result = $this->contactsEndpoint->setPageSize(250)->index();

        collect($result->content)->each(function($contact) {
            $this->assertObjectHasAttribute('id', $contact);

            $this->assertObjectHasAttribute('organizationId', $contact);
        });
    }

    public function testLexofficeGetSingleContact() {
        $contacts = $this->contactsEndpoint->index();

        $contact = collect($contacts->content)->random(1)->first();

        $result = $this->contactsEndpoint->get($contact->id);

        $this->assertEquals($result->id, $contact->id);
    }
}
