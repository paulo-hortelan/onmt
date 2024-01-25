<?php

namespace PauloHortelan\Onmt\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use PauloHortelan\Onmt\Models\Cto;
use PauloHortelan\Onmt\Models\Ont;

class OntFactory extends Factory
{
    protected $model = Ont::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'interface' => $this->faker->word(),
            'cto_id' => Cto::all()->random()->id,
        ];
    }
}
