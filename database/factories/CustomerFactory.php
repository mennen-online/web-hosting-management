<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user = User::factory()->create();
        return [
            'user_id' => $user->id,
            'customer_type' => 'person',
            'salutation' => '',
            'firstName' => $user->first_name,
            'lastName' => $user->last_name
        ];
    }

    public function configure() {
        return $this->afterCreating(function(Customer $customer) {
            $customer->update([
                'street_number' => 'Teststraße 123',
                'postcode' => '12345',
                'city' => 'Testort',
                'countryCode' => 'DE'
            ]);

            $customer = Customer::find($customer->id);

            CustomerContact::factory()->for($customer)->create();
        });
    }
}
