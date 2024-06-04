<?php

use PauloHortelan\Onmt\Facades\Nokia;
use PauloHortelan\Onmt\Models\Ceo;
use PauloHortelan\Onmt\Models\CeoSplitter;
use PauloHortelan\Onmt\Models\Cto;
use PauloHortelan\Onmt\Models\Dio;
use PauloHortelan\Onmt\Models\Olt;
use PauloHortelan\Onmt\Models\Ont;
use PauloHortelan\Onmt\Services\Nokia\NokiaService;

uses()->group('Nokia');

beforeEach(function () {
    $this->correctInterface = '1/1/1/1/8';
    $this->wrongInterface = '1/1/3/20/1';

    $this->correctSerial = 'ALCLFC5A84A7';
    $this->wrongSerial = 'ALCLB40D2CC1';

    $this->olt = Olt::create([
        'name' => 'olt-test1',
        'host_connection' => '127.0.1.100',
        'host_server' => '127.0.1.100',
        'username' => 'user',
        'password' => 'pass1234',
        'brand' => 'Nokia',
        'model' => 'FX16',
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

    Cto::create([
        'name' => 'SP01',
        'type' => '1x8',
        'ceo_splitter_id' => 1,
    ]);

    $this->ont = Ont::create([
        'name' => 'ALCLFC5A84A7',
        'interface' => '1/1/1/1/8',
        'cto_id' => 1,
    ]);
});

describe('connection', function () {
    it('can connect on telnet', function () {
        $nokia = Nokia::connect($this->olt);

        expect($nokia)->toBeInstanceOf(NokiaService::class);
    });

    it('can close connection', function () {
        $nokia = Nokia::connect($this->olt);
        $nokia->disconnect();

        $nokia->interface($this->correctInterface)->opticalPower();
    })->throws(Exception::class);
})->skipIfFakeConnection();

describe('optical-power', function () {
    it('can get ont optical power with olt + interface', function () {
        $opticalPower = Nokia::connect($this->olt)->interface($this->correctInterface)->opticalPower();

        expect($opticalPower)->toBeFloat();
    });

    it('can get ont optical power with ont', function () {
        $opticalPower = Nokia::ont($this->ont)->opticalPower();

        expect($opticalPower)->toBeFloat();
    });

    it('can get multiple ont optical power with olt + interfaces', function () {
        $opticalPower = Nokia::connect($this->olt)->interfaces([
            '1/1/1/1/8',
            '1/1/1/1/7',
            '1/1/1/1/4',
        ])->opticalPower();

        expect($opticalPower)->toBeArray()->toHaveCount(3);
        expect($opticalPower[0])->toBeFloat();
        expect($opticalPower[1])->toBeFloat();
        expect($opticalPower[2])->toBeFloat();
    });

    it('can get multiple ont optical power with onts', function () {
        Ont::create([
            'name' => 'CMSZ3B12CA43',
            'interface' => '1/1/1/1/4',
            'cto_id' => 1,
        ]);

        Ont::create([
            'name' => 'ALCLFC5ABE9F',
            'interface' => '1/1/1/1/9',
            'cto_id' => 1,
        ]);

        $onts = Ont::all();

        $opticalPower = Nokia::onts($onts)->opticalPower();

        expect($opticalPower)->toBeArray()->toHaveCount(3);
        expect($opticalPower[0])->toBeFloat();
        expect($opticalPower[1])->toBeFloat();
        expect($opticalPower[2])->toBeFloat();
    });

    it('returns null when cannot get ont optical power', function () {
        $opticalPower = Nokia::connect($this->olt)->interface($this->wrongInterface)->opticalPower();

        expect($opticalPower)->toBeNull();
    });
})->skipIfFakeConnection();

describe('optical-interface', function () {
    it('can get ont optical interface with olt + serial', function () {
        $interface = Nokia::connect($this->olt)->serial($this->correctSerial)->opticalInterface();

        $this->assertNotNull($interface);
    });

    it('can get ont optical interface with ont', function () {
        $interface = Nokia::ont($this->ont)->opticalInterface();

        $this->assertNotNull($interface);
    });

    it('can get multiple ont interface with olt + serials', function () {
        $opticalInterface = Nokia::connect($this->olt)->serials([
            'CMSZ3B12CA43',
            'ALCLFC5ABE9F',
        ])->opticalInterface();

        expect($opticalInterface)->toBeArray()->toHaveCount(2);
        expect($opticalInterface[0])->toBeString();
        expect($opticalInterface[1])->toBeString();
    });

    it('can get multiple ont interface with onts', function () {
        Ont::create([
            'name' => 'CMSZ3B12CA43',
            'interface' => '1/1/1/1/4',
            'cto_id' => 1,
        ]);

        Ont::create([
            'name' => 'ALCLFC5ABE9F',
            'interface' => '1/1/1/1/9',
            'cto_id' => 1,
        ]);

        $onts = Ont::all();

        $opticalPower = Nokia::onts($onts)->opticalInterface();

        expect($opticalPower)->toBeArray()->toHaveCount(3);
        expect($opticalPower[0])->toBeString();
        expect($opticalPower[1])->toBeString();
        expect($opticalPower[2])->toBeString();
    });

    it('returns null when cannot get ont interface', function () {
        $opticalInterface = Nokia::connect($this->olt)->serial($this->wrongSerial)->opticalInterface();

        expect($opticalInterface)->toBeNull();
    });
})->skipIfFakeConnection();
