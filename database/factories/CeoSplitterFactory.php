<?php

namespace PauloHortelan\Onmt\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use PauloHortelan\Onmt\Models\Ceo;
use PauloHortelan\Onmt\Models\CeoSplitter;

class CeoSplitterFactory extends Factory
{
    protected $model = CeoSplitter::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'type' => $this->faker->randomElement(['1x8', '1x16']),
            'slot' => $this->faker->numberBetween(1, 100),
            'pon' => $this->faker->numberBetween(1, 100),
            'ceo_id' => Ceo::all()->random()->id,
        ];
    }
}
