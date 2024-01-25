<?php

use PauloHortelan\Onmt\Facades\ZTE;
use PauloHortelan\Onmt\Models\Ceo;
use PauloHortelan\Onmt\Models\CeoSplitter;
use PauloHortelan\Onmt\Models\Cto;
use PauloHortelan\Onmt\Models\Dio;
use PauloHortelan\Onmt\Models\Olt;
use PauloHortelan\Onmt\Models\Ont;
use PauloHortelan\Onmt\Services\ZTE\ZTEService;

uses()->group('ZTE-C300');

beforeEach(function () {
    $this->correctInterface = 'gpon-onu_1/2/1:61';
    $this->wrongInterface = 'gpon-onu_1/2/1:99';

    $this->correctSerial = 'CMSZ3B112933';
    $this->wrongSerial = 'ALCLB40D2CC1';

    $this->olt = Olt::create([
        'name' => 'olt-test1',
        'host' => '127.0.1.200',
        'username' => 'user',
        'password' => 'pass1234',
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

    Cto::create([
        'name' => 'SP01',
        'type' => '1x8',
        'ceo_splitter_id' => 1,
    ]);

    $this->ont = Ont::create([
        'name' => 'CMSZ3B112933',
        'interface' => 'gpon-onu_1/2/1:37',
        'cto_id' => 1,
    ]);
});

describe('connection', function () {
    it('can connect on telnet', function () {
        $zte = ZTE::connect($this->olt);

        expect($zte)->toBeInstanceOf(ZTEService::class);
    });

    it('can close connection', function () {
        $zte = ZTE::connect($this->olt);
        $zte->disconnect();

        $zte->interface($this->correctInterface)->opticalPower();
    })->throws(Exception::class);
})->skipIfFakeConnection();

describe('optical-power', function () {
    it('can get ont optical power with olt + interface', function () {
        $opticalPower = ZTE::connect($this->olt)->interface($this->correctInterface)->opticalPower();

        expect($opticalPower)->toBeFloat();
    });

    it('can get ont optical power with ont', function () {
        $opticalPower = ZTE::ont($this->ont)->opticalPower();

        expect($opticalPower)->toBeFloat();
    });

    it('can get multiple ont optical power with olt + interfaces', function () {
        $opticalPower = ZTE::connect($this->olt)->interfaces([
            'gpon-onu_1/2/1:21',
            'gpon-onu_1/2/1:66',
            'gpon-onu_1/2/1:63',
        ])->opticalPower();

        expect($opticalPower)->toBeArray()->toHaveCount(3);
        expect($opticalPower[0])->toBeFloat();
        expect($opticalPower[1])->toBeFloat();
        expect($opticalPower[2])->toBeFloat();
    });

    it('can get multiple ont optical power with onts', function () {
        Ont::create([
            'name' => 'ALCLFC578DBE',
            'interface' => 'gpon-onu_1/2/1:48',
            'cto_id' => 1,
        ]);

        Ont::create([
            'name' => 'CMSZ3B112FC9',
            'interface' => 'gpon-onu_1/2/1:31',
            'cto_id' => 1,
        ]);

        $onts = Ont::all();

        $opticalPower = ZTE::onts($onts)->opticalPower();

        expect($opticalPower)->toBeArray()->toHaveCount(3);
        expect($opticalPower[0])->toBeFloat();
        expect($opticalPower[1])->toBeFloat();
        expect($opticalPower[2])->toBeFloat();
    });

    it('throws exception when cannot get ont optical power', function () {
        ZTE::connect($this->olt)->interface($this->wrongInterface)->opticalPower();
    })->throws(Exception::class);
})->skipIfFakeConnection();

describe('optical-interface', function () {
    it('can get ont optical interface with olt + serial', function () {
        $interface = ZTE::connect($this->olt)->serial($this->correctSerial)->opticalInterface();

        $this->assertNotNull($interface);
    });

    it('can get ont optical interface with ont', function () {
        $interface = ZTE::ont($this->ont)->opticalInterface();

        $this->assertNotNull($interface);
    });

    it('can get multiple ont interface with olt + serials', function () {
        $opticalInterface = ZTE::connect($this->olt)->serials([
            'ALCLFC576CBC',
            'ALCLB4079DB1',
        ])->opticalInterface();

        expect($opticalInterface)->toBeArray()->toHaveCount(2);
        expect($opticalInterface[0])->toBeString();
        expect($opticalInterface[1])->toBeString();
    });

    it('can get multiple ont interface with onts', function () {
        Ont::create([
            'name' => 'CMSZ3B112FC9',
            'interface' => 'gpon-onu_1/2/1:31',
            'cto_id' => 1,
        ]);

        Ont::create([
            'name' => 'CMSZ3B113098',
            'interface' => 'gpon-onu_1/2/1:32',
            'cto_id' => 1,
        ]);

        $onts = Ont::all();

        $opticalPower = ZTE::onts($onts)->opticalInterface();

        expect($opticalPower)->toBeArray()->toHaveCount(3);
        expect($opticalPower[0])->toBeString();
        expect($opticalPower[1])->toBeString();
        expect($opticalPower[2])->toBeString();
    });

    it('throws exception when cannot get ont interface', function () {
        ZTE::connect($this->olt)->serial($this->wrongSerial)->opticalInterface();
    })->throws(Exception::class);

    it('can get multiple ont interfaces with olt + serials', function () {
        $opticalPower = ZTE::connect($this->olt)->interfaces([
            'gpon-onu_1/2/1:32',
            'gpon-onu_1/2/1:50',
            'gpon-onu_1/2/1:60',
        ])->opticalPower();

        expect($opticalPower)->toBeArray()->toHaveCount(3);
        expect($opticalPower[0])->toBeFloat();
        expect($opticalPower[1])->toBeFloat();
        expect($opticalPower[2])->toBeFloat();
    });
})->skipIfFakeConnection();
