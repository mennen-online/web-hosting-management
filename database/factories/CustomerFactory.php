<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerAddress;
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
        return [
            'customer_type' => 'person',
            'salutation' => '',
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->safeEmail,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Customer $customer) {
            CustomerAddress::factory()->create([
                'customer_id' => $customer->id,
                'type' => 'billing',
                'street' => $this->faker->streetAddress,
                'supplement' => '',
                'zip' => $this->faker->postcode,
                'city' => $this->faker->city,
                'country_code' => $this->faker->countryCode
            ]);

            $customer = Customer::find($customer->id);

            CustomerContact::factory()->for($customer)->create();
        });
    }
}
