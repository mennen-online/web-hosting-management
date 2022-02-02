<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerContact;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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
            'lexoffice_id' => $this->faker->uuid,
            'salutation' => '',
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->safeEmail,
        ];
    }

    public function company()
    {
        return $this->state(function (array $attributes) {
            return [
                'customer_type' => 'company',
                'companyName' => $this->faker->company,
                'allowTaxFreeInvoices' => $this->faker->boolean,
                'taxNumber' => $this->faker->numberBetween(),
                'vatRegistrationId' => 'DE'.$this->faker->numberBetween(100000000, 999999999)
            ];
        });
    }
}
