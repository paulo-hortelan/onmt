<?php

use PauloHortelan\Onmt\Models\Ceo;
use PauloHortelan\Onmt\Models\CeoSplitter;
use PauloHortelan\Onmt\Models\Cto;
use PauloHortelan\Onmt\Models\Dio;
use PauloHortelan\Onmt\Models\Olt;

beforeEach(function () {
    Olt::factory()->count(5)->has(
        Dio::factory()->count(5)->has(
            Ceo::factory()->count(5)
        )
    )->create();

    CeoSplitter::factory()->count(5)->create();
});

it('does not create an ceo without required fields', function () {
    $cto = [];

    $this->post(route('ctos.store'), $cto)->assertStatus(302);
});

it('can create a cto', function () {
    $this->withoutExceptionHandling();
    $cto = Cto::factory()->raw(['name' => 'cto-test']);

    $this->post(route('ctos.store'), $cto)->assertStatus(201);

    $this->assertDatabaseHas('ctos', ['name' => 'cto-test']);
});

it('can fetch a cto', function () {
    $cto = Cto::factory()->create(['name' => 'cto-test']);
    $this->get(route('ctos.show', $cto))->assertStatus(200);
});

it('can update a cto', function () {
    $this->withoutExceptionHandling();
    $cto = Cto::factory()->raw(['name' => 'cto-test']);

    $this->post(route('ctos.store'), $cto)->assertStatus(201);
    $this->assertDatabaseHas('ctos', ['name' => 'cto-test']);

    $oldCto = Cto::firstWhere('name', 'cto-test');
    $newCto = Cto::factory()->raw(['name' => 'cto-new-test']);

    $this->put(route('ctos.update', $oldCto), $newCto)->assertStatus(200);

    $this->assertDatabaseMissing('ctos', ['name' => 'cto-test']);
    $this->assertDatabaseHas('ctos', ['name' => 'cto-new-test']);
});

it('can delete a cto', function () {
    $cto = Cto::factory()->raw(['name' => 'cto-test']);

    $this->post(route('ctos.store'), $cto)->assertStatus(201);
    $this->assertDatabaseHas('ctos', ['name' => 'cto-test']);

    $oldCto = Cto::firstWhere('name', 'cto-test');

    $this->delete(route('ctos.destroy', $oldCto))->assertStatus(204);

    $this->assertDatabaseMissing('ctos', ['name' => 'cto-test']);
});
