<?php

namespace Database\Factories;

use App\Models\Server;
use App\Services\Forge\Endpoints\ServersEndpoint;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Server::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'forge_id' => app()->make(ServersEndpoint::class)->create()->server->id
        ];
    }
}
