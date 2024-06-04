<?php

namespace PauloHortelan\Onmt\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use PauloHortelan\Onmt\Models\Olt;

class OltFactory extends Factory
{
    protected $model = Olt::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'host_connection' => $this->faker->ipv4(),
            'host_server' => $this->faker->ipv4(),
            'username' => $this->faker->userName(),
            'password' => $this->faker->password(),
            'brand' => $this->faker->word(),
            'model' => $this->faker->word(),
        ];
    }
}
