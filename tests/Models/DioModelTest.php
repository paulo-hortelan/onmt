<?php

use Illuminate\Database\QueryException;
use PauloHortelan\Onmt\Models\Dio;
use PauloHortelan\Onmt\Models\Olt;

uses()->group('DIO-Model');

beforeEach(function () {
    $this->olt = Olt::create([
        'name' => 'olt-test1',
        'host' => '127.0.0.1',
        'username' => 'test',
        'password' => '1234',
        'brand' => 'ZTE',
        'model' => 'C300',
        'interface' => 'gpon-onu_1/',
    ]);
});

it('can create', function () {
    $this->assertNotNull($this->olt);
    $this->assertEquals(1, Olt::count());

    $dio = Dio::create([
        'name' => 'dio-test1',
        'olt_id' => 1,
    ]);

    $this->assertNotNull($dio);
    $this->assertEquals(1, Dio::count());

    $oltName = Dio::find(1)->olt->name;

    $this->assertEquals($oltName, 'olt-test1');
});

it('cannot create when olt doesnt exist', function () {
    $this->assertNotNull($this->olt);
    $this->assertEquals(1, Olt::count());

    Dio::create([
        'name' => 'dio-test2',
        'olt_id' => 2,
    ]);
})->throws(QueryException::class);
