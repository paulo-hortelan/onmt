<?php

use PauloHortelan\Onmt\Models\Dio;
use PauloHortelan\Onmt\Models\Olt;

uses()->group('Routes');

beforeEach(function () {
    Olt::factory()->count(5)->create();
});

it('does not create an dio without required fields', function () {
    $dio = [];

    $this->post(route('dios.store'), $dio)->assertStatus(302);
});

it('can create an dio', function () {
    $dio = Dio::factory()->raw(['name' => 'dio-test']);

    $this->post(route('dios.store'), $dio)->assertStatus(201);

    $this->assertDatabaseHas('dios', ['name' => 'dio-test']);
});

it('can fetch an dio', function () {
    $dio = Dio::factory()->create(['name' => 'dio-test']);
    $this->get(route('dios.show', $dio))->assertStatus(200);
});

it('can update an dio', function () {
    $dio = Dio::factory()->raw(['name' => 'dio-test']);

    $this->post(route('dios.store'), $dio)->assertStatus(201);
    $this->assertDatabaseHas('dios', ['name' => 'dio-test']);

    $oldDio = Dio::firstWhere('name', 'dio-test');
    $newDio = Dio::factory()->raw(['name' => 'dio-new-test']);

    $this->put(route('dios.update', $oldDio), $newDio)->assertStatus(200);

    $this->assertDatabaseMissing('dios', ['name' => 'dio-test']);
    $this->assertDatabaseHas('dios', ['name' => 'dio-new-test']);
});

it('can delete an dio', function () {
    $dio = Dio::factory()->raw(['name' => 'dio-test']);

    $this->post(route('dios.store'), $dio)->assertStatus(201);
    $this->assertDatabaseHas('dios', ['name' => 'dio-test']);

    $oldDio = Dio::firstWhere('name', 'dio-test');

    $this->delete(route('dios.destroy', $oldDio))->assertStatus(204);

    $this->assertDatabaseMissing('dios', ['name' => 'dio-test']);
});
