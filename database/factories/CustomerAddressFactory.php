<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'type' => 'billing',
            'street' => $this->faker->streetAddress,
            'supplement' => '',
            'zip' => $this->faker->postcode,
            'city' => $this->faker->city,
            'country_code' => $this->faker->countryCode
        ];
    }
}
