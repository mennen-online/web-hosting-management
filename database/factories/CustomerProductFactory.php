<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerProduct;
use App\Models\Domain;
use App\Models\Product;
use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CustomerProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'customer_id' => Customer::factory()->create()->id,
            'product_id' => Product::factory()->create()->id,
            'domain_id' => Domain::factory()->create()->id,
            'server_id' => Server::factory()->create()->id
        ];
    }
}
