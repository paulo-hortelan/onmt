<?php

use PauloHortelan\Onmt\Models\Ceo;
use PauloHortelan\Onmt\Models\CeoSplitter;
use PauloHortelan\Onmt\Models\Cto;
use PauloHortelan\Onmt\Models\Dio;
use PauloHortelan\Onmt\Models\Olt;
use PauloHortelan\Onmt\Models\Ont;

beforeEach(function () {
    Olt::factory()->count(5)->has(
        Dio::factory()->count(5)->has(
            Ceo::factory()->count(5)
        )
    )->create();

    CeoSplitter::factory()->count(5)->has(
        Cto::factory()->count(10)
    )->create();
});

it('does not create an ont without required fields', function () {
    $ont = [];

    $this->post(route('onts.store'), $ont)->assertStatus(302);
});

it('can create an ont', function () {
    $this->withoutExceptionHandling();
    $ont = Ont::factory()->raw(['name' => 'CMSZ123451']);

    $this->post(route('onts.store'), $ont)->assertStatus(201);

    $this->assertDatabaseHas('onts', ['name' => 'CMSZ123451']);
});

it('can fetch an ont', function () {
    $ont = Ont::factory()->create(['name' => 'CMSZ123451']);
    $this->get(route('onts.show', $ont))->assertStatus(200);
});

it('can update an ont', function () {
    $this->withoutExceptionHandling();
    $ont = Ont::factory()->raw(['name' => 'CMSZ123451']);

    $this->post(route('onts.store'), $ont)->assertStatus(201);
    $this->assertDatabaseHas('onts', ['name' => 'CMSZ123451']);

    $oldOnt = Ont::firstWhere('name', 'CMSZ123451');
    $newOnt = Ont::factory()->raw(['name' => 'ALCL1381723']);

    $this->put(route('onts.update', $oldOnt), $newOnt)->assertStatus(200);

    $this->assertDatabaseMissing('onts', ['name' => 'CMSZ123451']);
    $this->assertDatabaseHas('onts', ['name' => 'ALCL1381723']);
});

it('can delete an ont', function () {
    $ont = Ont::factory()->raw(['name' => 'CMSZ123451']);

    $this->post(route('onts.store'), $ont)->assertStatus(201);
    $this->assertDatabaseHas('onts', ['name' => 'CMSZ123451']);

    $oldOnt = Ont::firstWhere('name', 'CMSZ123451');

    $this->delete(route('onts.destroy', $oldOnt))->assertStatus(204);

    $this->assertDatabaseMissing('onts', ['name' => 'CMSZ123451']);
});
