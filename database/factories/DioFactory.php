<?php

namespace PauloHortelan\OltMonitoring\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use PauloHortelan\OltMonitoring\Models\Dio;
use PauloHortelan\OltMonitoring\Models\Olt;

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
