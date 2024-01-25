<?php

use Illuminate\Database\QueryException;
use PauloHortelan\Onmt\Models\Ceo;
use PauloHortelan\Onmt\Models\Dio;
use PauloHortelan\Onmt\Models\Olt;

uses()->group('CEO-Model');

beforeEach(function () {
    Olt::create([
        'name' => 'olt-test1',
        'host' => '127.0.0.1',
        'username' => 'test',
        'password' => '1234',
        'brand' => 'ZTE',
        'model' => 'C300',
    ]);

    $this->dio = Dio::create([
        'name' => 'dio-test1',
        'olt_id' => 1,
    ]);
});

it('can create', function () {
    $this->assertNotNull($this->dio);
    $this->assertEquals(1, Dio::count());

    $ceo = Ceo::create([
        'name' => 'BB01-CX01',
        'dio_id' => 1,
    ]);

    $this->assertNotNull($ceo);
    $this->assertEquals(1, Ceo::count());

    $dioName = Ceo::find(1)->dio->name;

    $this->assertEquals($dioName, 'dio-test1');
});

it('cannot create when dio doesnt exist', function () {
    $this->assertNotNull($this->dio);
    $this->assertEquals(1, Dio::count());

    Ceo::create([
        'name' => 'BB01-CX01',
        'dio_id' => 2,
    ]);
})->throws(QueryException::class);
