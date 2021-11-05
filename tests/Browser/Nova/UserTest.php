<?php

namespace Tests\Browser\Nova;

use App\Models\User;
use Arr;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Users;
use Tests\DuskTestCase;

class UserTest extends DuskTestCase
{
    use WithFaker;

    protected function setUp(): void {
        parent::setUp();
    }

    public function testCreateUser() {
        $this->browse(function(Browser $browser) {
            $user = [
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'email' => $this->faker->unique->safeEmail,
                'password' => $this->faker->password
            ];
            $browser
                ->loginAs(User::first())
                ->visit(new Users)
                ->assertPathIs('/nova/resources/users')
                ->click('@create-button')
                ->waitForLocation('/nova/resources/users/new')
                ->screenshot('create_user_screen')
                ->waitForText('Create User')
                ->type('@first_name', $user['first_name'])
                ->type('@last_name', $user['last_name'])
                ->type('@email', $user['email'])
                ->type('@password', $user['password'])
                ->click('@create-button')
                ->screenshot('create_user_button_clicked')
                ->waitForText($user['first_name'])
                ->click('@users-resource-link')
                ->waitForText($user['first_name']);

            $this->assertDatabaseHas('users', Arr::except($user, 'password'));
        });
    }
}
