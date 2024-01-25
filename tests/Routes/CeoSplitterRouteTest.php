<?php

use PauloHortelan\Onmt\Models\Ceo;
use PauloHortelan\Onmt\Models\CeoSplitter;
use PauloHortelan\Onmt\Models\Dio;
use PauloHortelan\Onmt\Models\Olt;

beforeEach(function () {
    Olt::factory()->count(5)->has(
        Dio::factory()->count(5)->has(
            Ceo::factory()->count(5)
        )
    )->create();
});

it('does not create a ceo splitter without required fields', function () {
    $ceoSplitter = [];

    $this->post(route('ceo-splitters.store'), $ceoSplitter)->assertStatus(302);
});

it('can create a ceo splitter', function () {
    $ceoSplitter = CeoSplitter::factory()->raw(['name' => 'FTTH-100']);

    $this->post(route('ceo-splitters.store'), $ceoSplitter)->assertStatus(201);

    $this->assertDatabaseHas('ceo_splitters', ['name' => 'FTTH-100']);
});

it('can fetch a ceo splitter', function () {
    $ceoSplitter = CeoSplitter::factory()->create(['name' => 'FTTH-100']);
    $this->get(route('ceo-splitters.show', $ceoSplitter))->assertStatus(200);
});

it('can update a ceo splitter', function () {
    $ceoSplitter = CeoSplitter::factory()->raw(['name' => 'FTTH-100']);

    $this->post(route('ceo-splitters.store'), $ceoSplitter)->assertStatus(201);
    $this->assertDatabaseHas('ceo_splitters', ['name' => 'FTTH-100']);

    $oldCeoSplitter = CeoSplitter::firstWhere('name', 'FTTH-100');
    $newCeoSplitter = CeoSplitter::factory()->raw(['name' => 'FTTH-200']);

    $this->put(route('ceo-splitters.update', $oldCeoSplitter), $newCeoSplitter)->assertStatus(200);

    $this->assertDatabaseMissing('ceo_splitters', ['name' => 'FTTH-100']);
    $this->assertDatabaseHas('ceo_splitters', ['name' => 'FTTH-200']);
});

it('can delete a ceo splitter', function () {
    $ceoSplitter = CeoSplitter::factory()->raw(['name' => 'FTTH-100']);

    $this->post(route('ceo-splitters.store'), $ceoSplitter)->assertStatus(201);
    $this->assertDatabaseHas('ceo_splitters', ['name' => 'FTTH-100']);

    $oldCeoSplitter = CeoSplitter::firstWhere('name', 'FTTH-100');

    $this->delete(route('ceo-splitters.destroy', $oldCeoSplitter))->assertStatus(204);

    $this->assertDatabaseMissing('ceo_splitters', ['name' => 'FTTH-100']);
});
