<?php

use Illuminate\Database\QueryException;
use PauloHortelan\Onmt\Models\Ceo;
use PauloHortelan\Onmt\Models\CeoSplitter;
use PauloHortelan\Onmt\Models\Dio;
use PauloHortelan\Onmt\Models\Olt;

uses()->group('Models');

beforeEach(function () {
    Olt::create([
        'name' => 'olt-test1',
        'host_connection' => '127.0.0.1',
        'host_server' => '127.0.0.1',
        'username' => 'test',
        'password' => '1234',
        'brand' => 'ZTE',
        'model' => 'C300',
        'interface' => 'gpon-onu_1/',
    ]);

    Dio::create([
        'name' => 'dio-test1',
        'olt_id' => 1,
    ]);

    $this->ceo = Ceo::create([
        'name' => 'BB01-CX01',
        'dio_id' => 1,
    ]);
});

it('can create', function () {
    $this->assertNotNull($this->ceo);
    $this->assertEquals(1, Ceo::count());

    $ceoSplitter = CeoSplitter::create([
        'name' => 'FTTH-101',
        'type' => '1x8',
        'slot' => 1,
        'pon' => 1,
        'ceo_id' => 1,
    ]);

    $this->assertNotNull($ceoSplitter);
    $this->assertEquals(1, CeoSplitter::count());

    $ceoName = CeoSplitter::find(1)->ceo->name;

    $this->assertEquals($ceoName, 'BB01-CX01');
});

it('cannot create when ceo doesnt exist', function () {
    $this->assertNotNull($this->ceo);
    $this->assertEquals(1, Ceo::count());

    CeoSplitter::create([
        'name' => 'FTTH-101',
        'type' => '1x8',
        'slot' => 1,
        'pon' => 1,
        'ceo_id' => 2,
    ]);
})->throws(QueryException::class);

it('cannot create when type is not valid', function () {
    $this->assertNotNull($this->ceo);
    $this->assertEquals(1, Ceo::count());

    CeoSplitter::create([
        'name' => 'FTTH-101',
        'type' => '123',
        'slot' => 1,
        'pon' => 1,
        'ceo_id' => 1,
    ]);
})->throws(QueryException::class);
