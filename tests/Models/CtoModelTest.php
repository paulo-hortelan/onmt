<?php

use Illuminate\Database\QueryException;
use PauloHortelan\Onmt\Models\Ceo;
use PauloHortelan\Onmt\Models\CeoSplitter;
use PauloHortelan\Onmt\Models\Cto;
use PauloHortelan\Onmt\Models\Dio;
use PauloHortelan\Onmt\Models\Olt;

uses()->group('CTO-Model');

beforeEach(function () {
    Olt::create([
        'name' => 'olt-test1',
        'host' => '127.0.0.1',
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

    Ceo::create([
        'name' => 'BB01-CX01',
        'dio_id' => 1,
    ]);

    $this->ceoSplitter = CeoSplitter::create([
        'name' => 'FTTH-101',
        'type' => '1x8',
        'slot' => 1,
        'pon' => 1,
        'ceo_id' => 1,
    ]);
});

it('can create', function () {
    $this->assertNotNull($this->ceoSplitter);
    $this->assertEquals(1, CeoSplitter::count());

    $cto = Cto::create([
        'name' => 'SP01',
        'type' => '1x8',
        'ceo_splitter_id' => 1,
    ]);

    $this->assertNotNull($cto);
    $this->assertEquals(1, Cto::count());

    $ceoSplitterName = Cto::find(1)->ceo_splitter->name;

    $this->assertEquals($ceoSplitterName, 'FTTH-101');

    $ceoName = Cto::find(1)->ceo_splitter->ceo->name;

    $this->assertEquals($ceoName, 'BB01-CX01');
});

it('cannot create when ceo splitter doesnt exist', function () {
    $this->assertNotNull($this->ceoSplitter);
    $this->assertEquals(1, CeoSplitter::count());

    Cto::create([
        'name' => 'SP01',
        'type' => '1x8',
        'ceo_splitter_id' => 2,
    ]);
})->throws(QueryException::class);

it('cannot create when type is not valid', function () {
    $this->assertNotNull($this->ceoSplitter);
    $this->assertEquals(1, CeoSplitter::count());

    Cto::create([
        'name' => 'SP01',
        'type' => '123',
        'ceo_splitter_id' => 1,
    ]);
})->throws(QueryException::class);
