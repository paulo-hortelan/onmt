<?php

namespace PauloHortelan\OltMonitoring\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use PauloHortelan\OltMonitoring\Models\Ceo;
use PauloHortelan\OltMonitoring\Models\Dio;

class CeoFactory extends Factory
{
    protected $model = Ceo::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'dio_id' => Dio::all()->random()->id,
        ];
    }
}
