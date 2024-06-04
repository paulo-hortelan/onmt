<?php

use PauloHortelan\Onmt\Facades\Fiberhome;
use PauloHortelan\Onmt\Models\Ceo;
use PauloHortelan\Onmt\Models\CeoSplitter;
use PauloHortelan\Onmt\Models\Cto;
use PauloHortelan\Onmt\Models\Dio;
use PauloHortelan\Onmt\Models\Olt;
use PauloHortelan\Onmt\Models\Ont;
use PauloHortelan\Onmt\Services\Fiberhome\FiberhomeService;

uses()->group('Fiberhome');

beforeEach(function () {
    $this->correctInterface = 'NA-NA-11-5';
    $this->wrongInterface = 'NA-NA';

    $this->correctSerial = 'FHTT0134FE0F';
    $this->wrongSerial = 'ALCLB44D2CC1';

    $this->olt = Olt::create([
        'name' => 'olt-test1',
        'host_connection' => '127.0.0.1',
        'host_server' => '127.0.0.1',
        'username' => 'test',
        'password' => 'test',
        'brand' => 'Fiberhome',
        'model' => 'AN551604',
    ]);

    Olt::create([
        'name' => 'olt-graia3',
        'host_connection' => '127.0.0.1',
        'host_server' => '127.0.0.1',
        'username' => 'test',
        'password' => 'test',
        'brand' => 'Fiberhome',
        'model' => 'AN551604',
    ]);

    Dio::create([
        'name' => 'dio-test1',
        'olt_id' => 1,
    ]);

    Dio::create([
        'name' => 'dio-test2',
        'olt_id' => 2,
    ]);

    Ceo::create([
        'name' => 'BB01-CX01',
        'dio_id' => 1,
    ]);

    Ceo::create([
        'name' => 'BB01-CX02',
        'dio_id' => 2,
    ]);

    CeoSplitter::create([
        'name' => 'FTTH-101',
        'type' => '1x8',
        'slot' => 1,
        'pon' => 1,
        'ceo_id' => 1,
    ]);

    CeoSplitter::create([
        'name' => 'FTTH-222',
        'type' => '1x8',
        'slot' => 3,
        'pon' => 4,
        'ceo_id' => 2,
    ]);

    Cto::create([
        'name' => 'SP01',
        'type' => '1x8',
        'ceo_splitter_id' => 1,
    ]);

    Cto::create([
        'name' => 'SP03',
        'type' => '1x8',
        'ceo_splitter_id' => 2,
    ]);

    $this->ont = Ont::create([
        'name' => 'CMSZ3B391F2E',
        'interface' => 'NA-NA-11-5',
        'cto_id' => 1,
    ]);
});

describe('connection', function () {
    it('can connect on tl1', function () {
        $this->withoutExceptionHandling();
        $fiberhome = Fiberhome::connect($this->olt);

        expect($fiberhome)->toBeInstanceOf(FiberhomeService::class);
    });

    it('can close connection', function () {
        $fiberhome = Fiberhome::connect($this->olt);
        $fiberhome->disconnect();

        $fiberhome->interface($this->correctInterface)->serial($this->correctSerial)->opticalPower();
    })->throws(Exception::class);
})->skipIfFakeConnection();

describe('optical-power', function () {
    it('can get ont optical power with olt + interface + serial', function () {
        $opticalPower = Fiberhome::connect($this->olt)->interface($this->correctInterface)->serial($this->correctSerial)->opticalPower();

        expect($opticalPower)->toBeFloat();
    });

    it('can get ont optical power with ont', function () {
        $opticalPower = Fiberhome::ont($this->ont)->opticalPower();

        expect($opticalPower)->toBeFloat();
    });

    it('can get multiple ont optical power with olt + interfaces + serials', function () {
        $opticalPower = Fiberhome::connect($this->olt)->interfaces([
            'NA-NA-11-13',
            'NA-NA-11-13',
            'NA-NA-11-13',
        ])->serials([
            'CMSZ3B992FE9',
            'DD15B323DE58',
            'ALCLB4FF2C0E',
        ])->opticalPower();

        expect($opticalPower)->toBeArray()->toHaveCount(3);
        expect($opticalPower[0])->toBeFloat();
        expect($opticalPower[1])->toBeFloat();
        expect($opticalPower[2])->toBeFloat();
    });

    it('can get multiple ont optical power with onts', function () {
        Ont::create([
            'name' => 'CMSZ3B032RE9',
            'interface' => 'NA-NA-11-13',
            'cto_id' => 1,
        ]);

        Ont::create([
            'name' => 'DD15B363FT58',
            'interface' => 'NA-NA-11-13',
            'cto_id' => 1,
        ]);

        $onts = Ont::all();

        $opticalPower = Fiberhome::onts($onts)->opticalPower();

        expect($opticalPower)->toBeArray()->toHaveCount(3);
        expect($opticalPower[0])->toBeFloat();
        expect($opticalPower[1])->toBeFloat();
        expect($opticalPower[2])->toBeFloat();
    });

    it('retuns null when cannot get ont optical power', function () {
        $opticalPower = Fiberhome::connect($this->olt)->interface($this->wrongInterface)->serial($this->wrongSerial)->opticalPower();

        expect($opticalPower)->toBeNull();

        $opticalPower = Fiberhome::connect($this->olt)->interface($this->correctInterface)->serial($this->wrongSerial)->opticalPower();

        expect($opticalPower)->toBeNull();
    });
})->skipIfFakeConnection();
