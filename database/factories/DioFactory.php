<?php

namespace PauloHortelan\Onmt\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use PauloHortelan\Onmt\Models\Dio;
use PauloHortelan\Onmt\Models\Olt;

class DioFactory extends Factory
{
    protected $model = Dio::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'olt_id' => Olt::all()->random()->id,
        ];
    }
}
