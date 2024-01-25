<?php

namespace PauloHortelan\Onmt\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use PauloHortelan\Onmt\Models\Ceo;
use PauloHortelan\Onmt\Models\Dio;

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
