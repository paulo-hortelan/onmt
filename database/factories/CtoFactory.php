<?php

namespace PauloHortelan\Onmt\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use PauloHortelan\Onmt\Models\CeoSplitter;
use PauloHortelan\Onmt\Models\Cto;

class CtoFactory extends Factory
{
    protected $model = Cto::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'type' => $this->faker->randomElement(['1x8', '1x16']),
            'ceo_splitter_id' => CeoSplitter::all()->random()->id,
        ];
    }
}
