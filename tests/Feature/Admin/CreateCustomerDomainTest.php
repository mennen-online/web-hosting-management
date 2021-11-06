<?php

namespace Tests\Feature\Admin;

use App\Jobs\Internetworx\CreateDomain;
use App\Models\Domain;
use App\Models\Product;
use App\Models\User;
use App\Models\Customer;
use App\Notifications\Customer\DomainRegistrationSuccessful;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CreateCustomerDomainTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void {
        parent::setUp();
    }

    protected function tearDown(): void {
        parent::tearDown();
    }

    public function testCustomerValidDomainCanBeCreatedAndJobIsPushed() {
        Queue::fake();

        Notification::fake();

        Product::factory()->create([
            'name' => 'WordPress',
            'description' => 'Simple WordPress',
            'price' => 120
        ]);

        Product::factory()->create([
            'name' => 'de',
            'description' => 'Domain de',
            'price' => 5
        ]);

        $user = User::factory()->create();

        Customer::factory()->for($user)->create([
            'customer_type' => 'person',
            'salutation' => Arr::random(['Herr', 'Frau']),
            'firstName' => $this->faker->firstName,
            'lastName' => $this->faker->lastName
        ]);

        Domain::factory()->create(
            [
                'user_id' => $user->id
            ]
        );

        Queue::assertPushed(CreateDomain::class);
    }
}
