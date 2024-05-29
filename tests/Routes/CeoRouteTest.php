<?php

use PauloHortelan\Onmt\Models\Ceo;
use PauloHortelan\Onmt\Models\Dio;
use PauloHortelan\Onmt\Models\Olt;

uses()->group('Routes');

beforeEach(function () {
    Olt::factory()->count(5)->has(
        Dio::factory()->count(5)
    )->create();
});

it('does not create a ceo without required fields', function () {
    $ceo = [];

    $this->post(route('ceos.store'), $ceo)->assertStatus(302);
});

it('can create a ceo', function () {
    $ceo = Ceo::factory()->raw(['name' => 'ceo-test']);

    $this->post(route('ceos.store'), $ceo)->assertStatus(201);

    $this->assertDatabaseHas('ceos', ['name' => 'ceo-test']);
});

it('can fetch a ceo', function () {
    $ceo = Ceo::factory()->create(['name' => 'ceo-test']);
    $this->get(route('ceos.show', $ceo))->assertStatus(200);
});

it('can update a ceo', function () {
    $ceo = Ceo::factory()->raw(['name' => 'ceo-test']);

    $this->post(route('ceos.store'), $ceo)->assertStatus(201);
    $this->assertDatabaseHas('ceos', ['name' => 'ceo-test']);

    $oldCeo = Ceo::firstWhere('name', 'ceo-test');
    $newCeo = Ceo::factory()->raw(['name' => 'ceo-new-test']);

    $this->put(route('ceos.update', $oldCeo), $newCeo)->assertStatus(200);

    $this->assertDatabaseMissing('ceos', ['name' => 'ceo-test']);
    $this->assertDatabaseHas('ceos', ['name' => 'ceo-new-test']);
});

it('can delete a ceo', function () {
    $ceo = Ceo::factory()->raw(['name' => 'ceo-test']);

    $this->post(route('ceos.store'), $ceo)->assertStatus(201);
    $this->assertDatabaseHas('ceos', ['name' => 'ceo-test']);

    $oldCeo = Ceo::firstWhere('name', 'ceo-test');

    $this->delete(route('ceos.destroy', $oldCeo))->assertStatus(204);

    $this->assertDatabaseMissing('ceos', ['name' => 'ceo-test']);
});
