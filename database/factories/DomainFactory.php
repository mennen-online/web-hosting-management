<?php

namespace Database\Factories;

use App\Models\Domain;
use App\Models\DomainProduct;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DomainFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Domain::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'domain_product_id' => DomainProduct::factory()->create(),
            'name' => Str::random().'.de'
        ];
    }
}
