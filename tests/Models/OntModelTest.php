<?php

use Illuminate\Database\QueryException;
use PauloHortelan\OltMonitoring\Models\Ceo;
use PauloHortelan\OltMonitoring\Models\CeoSplitter;
use PauloHortelan\OltMonitoring\Models\Cto;
use PauloHortelan\OltMonitoring\Models\Dio;
use PauloHortelan\OltMonitoring\Models\Olt;
use PauloHortelan\OltMonitoring\Models\Ont;

uses()->group('ONT-Model');

beforeEach(function () {
    Olt::create([
        'name' => 'olt-test1',
        'host' => '127.0.0.1',
        'username' => 'test',
        'password' => '1234',
        'brand' => 'ZTE',
        'model' => 'C300',
    ]);

    Dio::create([
        'name' => 'dio-test1',
        'olt_id' => 1,
    ]);

    Ceo::create([
        'name' => 'BB01-CX01',
        'dio_id' => 1,
    ]);

    CeoSplitter::create([
        'name' => 'FTTH-101',
        'type' => '1x8',
        'slot' => 1,
        'pon' => 1,
        'ceo_id' => 1,
    ]);

    $this->cto = Cto::create([
        'name' => 'SP01',
        'type' => '1x8',
        'ceo_splitter_id' => 1,
    ]);
});

it('can create', function () {
    $this->assertNotNull($this->cto);
    $this->assertEquals(1, Cto::count());

    $ont = Ont::create([
        'name' => 'ALCLB40D2CC1',
        'interface' => 'gpon-onu_1/1/1:43',
        'cto_id' => 1,
    ]);

    $this->assertNotNull($ont);
    $this->assertEquals(1, Ont::count());

    $ctoName = Ont::find(1)->cto->name;

    $this->assertEquals($ctoName, 'SP01');

    $ceoSplitterName = Ont::find(1)->cto->ceo_splitter->name;

    $this->assertEquals($ceoSplitterName, 'FTTH-101');

    $ceoName = Ont::find(1)->cto->ceo_splitter->ceo->name;

    $this->assertEquals($ceoName, 'BB01-CX01');
});

it('cannot create when cto doesnt exist', function () {
    $this->assertNotNull($this->cto);
    $this->assertEquals(1, Cto::count());

    Ont::create([
        'name' => 'ALCLB40D2CC1',
        'interface' => 'gpon-onu_1/1/1:43',
        'cto_id' => 2,
    ]);
})->throws(QueryException::class);
