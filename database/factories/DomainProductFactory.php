<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DomainProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'tld' => 'de',
            'currency' => 'EUR',
            'reg_price' => $this->faker->numberBetween(0, 100),
            'renewal_price' => $this->faker->numberBetween(0, 100),
            'update_price' => 0,
            'restore_price' => $this->faker->numberBetween(0, 100),
            'transfer_price' => $this->faker->numberBetween(0, 100),
            'trade_price' => 0,
            'whois_protection_price' => 0
        ];
    }
}
