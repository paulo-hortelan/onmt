<?php

use PauloHortelan\Onmt\Models\Olt;

it('does not create an olt without required fields', function () {
    $olt = [];

    $this->post(route('olts.store'), $olt)->assertStatus(302);
});

it('can create an olt', function () {
    $olt = Olt::factory()->raw(['name' => 'olt-test']);

    $this->post(route('olts.store'), $olt)->assertStatus(201);

    $this->assertDatabaseHas('olts', ['name' => 'olt-test']);
});

it('can fetch an olt', function () {
    $olt = Olt::factory()->create(['name' => 'olt-test']);
    $this->get(route('olts.show', $olt))->assertStatus(200);
});

it('can update an olt', function () {
    $olt = Olt::factory()->raw(['name' => 'olt-test']);

    $this->post(route('olts.store'), $olt)->assertStatus(201);
    $this->assertDatabaseHas('olts', ['name' => 'olt-test']);

    $oldOlt = Olt::firstWhere('name', 'olt-test');
    $newOlt = Olt::factory()->raw(['name' => 'olt-new-test']);

    $this->put(route('olts.update', $oldOlt), $newOlt)->assertStatus(200);

    $this->assertDatabaseMissing('olts', ['name' => 'olt-test']);
    $this->assertDatabaseHas('olts', ['name' => 'olt-new-test']);
});

it('can delete an olt', function () {
    $olt = Olt::factory()->raw(['name' => 'olt-test']);

    $this->post(route('olts.store'), $olt)->assertStatus(201);
    $this->assertDatabaseHas('olts', ['name' => 'olt-test']);

    $oldOlt = Olt::firstWhere('name', 'olt-test');

    $this->delete(route('olts.destroy', $oldOlt))->assertStatus(204);

    $this->assertDatabaseMissing('olts', ['name' => 'olt-test']);
});
