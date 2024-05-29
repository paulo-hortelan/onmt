<?php

use PauloHortelan\Onmt\Facades\OltMonitor;
use PauloHortelan\Onmt\Facades\Onmt;
use PauloHortelan\Onmt\Facades\ZTE;
use PauloHortelan\Onmt\Models\Ceo;
use PauloHortelan\Onmt\Models\CeoSplitter;
use PauloHortelan\Onmt\Models\Cto;
use PauloHortelan\Onmt\Models\Dio;
use PauloHortelan\Onmt\Models\Olt;
use PauloHortelan\Onmt\Models\Ont;
use PauloHortelan\Onmt\Services\OltMonitorService;
use PauloHortelan\Onmt\Services\OnmtService;

uses()->group('olt-monitor');

beforeEach(function () {
    Olt::create([
        'name' => 'olt-test1',
        'host_connection' => '127.0.0.1',
        'host_server' => '127.0.0.1',
        'username' => 'test',
        'password' => 'test',
        'brand' => 'ZTE',
        'model' => 'C300',
    ]);

    Olt::create([
        'name' => 'olt-test2',
        'host_connection' => '127.0.0.1',
        'host_server' => '127.0.0.1',
        'username' => 'test',
        'password' => 'test',
        'brand' => 'ZTE',
        'model' => 'C600',
    ]);

    Olt::create([
        'name' => 'olt-test3',
        'host_connection' => '127.0.0.1',
        'host_server' => '127.0.0.1',
        'username' => 'test',
        'password' => 'test',
        'brand' => 'Nokia',
        'model' => 'FX16',
    ]);

    Olt::create([
        'name' => 'olt-test1',
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
});

describe('connection', function () {
    it('can connect on telnet', function () {
        $olt = Olt::firstWhere([['brand', 'ZTE'], ['model', 'C300']]);
        $Onmt = Onmt::connect($olt);

        expect($Onmt)->toBeInstanceOf(OnmtService::class);
    });

    it('can connect on tl1', function () {
        $olt = Olt::firstWhere([['brand', 'Fiberhome'], ['model', 'AN551604']]);
        $Onmt = Onmt::connect($olt);

        expect($Onmt)->toBeInstanceOf(OnmtService::class);
    });
})->skipIfFakeConnection();

describe('optical power with olt + serials + interfaces', function () {
    it('can get multiple ZTE-C300', function () {
        $olt = Olt::firstWhere([['brand', 'ZTE'], ['model', 'C300']]);
        $opticalPower = Onmt::connect($olt)->interfaces([
            'gpon-onu_1/2/1:21',
            'gpon-onu_1/2/1:66',
            'gpon-onu_1/2/1:31',
        ])->opticalPower();

        expect($opticalPower)->toBeArray()->toHaveCount(3);
        expect($opticalPower[0])->toBeFloat();
        expect($opticalPower[1])->toBeFloat();
        expect($opticalPower[2])->toBeFloat();
    });

    it('can get multiple ZTE-C600', function () {
        $olt = Olt::firstWhere([['brand', 'ZTE'], ['model', 'C600']]);
        $opticalPower = Onmt::connect($olt)->interfaces([
            'gpon_onu-1/1/1:3',
            'gpon_onu-1/1/1:8',
            'gpon_onu-1/1/1:7',
        ])->opticalPower();

        expect($opticalPower)->toBeArray()->toHaveCount(3);
        expect($opticalPower[0])->toBeFloat();
        expect($opticalPower[1])->toBeFloat();
        expect($opticalPower[2])->toBeFloat();
    });

    it('can get multiple Nokia-FX16', function () {
        $olt = Olt::firstWhere([['brand', 'Nokia'], ['model', 'FX16']]);
        $opticalPower = Onmt::connect($olt)->interfaces([
            '1/1/1/1/8',
            '1/1/1/1/7',
            '1/1/1/1/4',
            '1/1/1/1/5',
        ])->opticalPower();

        expect($opticalPower)->toBeArray()->toHaveCount(4);
        expect($opticalPower[0])->toBeFloat();
        expect($opticalPower[1])->toBeFloat();
        expect($opticalPower[2])->toBeFloat();
        expect($opticalPower[3])->toBeFloat();
    });

    it('can get multiple Fiberhome-AN551604', function () {
        $olt = Olt::firstWhere([['brand', 'Fiberhome'], ['model', 'AN551604']]);
        $opticalPower = Onmt::connect($olt)->interfaces([
            'NA-NA-11-13',
            'NA-NA-11-13',
            'NA-NA-11-13',
        ])->serials([
            'CMSZ3B092FE9',
            'DD15B363DD58',
            'ALCLB40D2C0E',
        ])->opticalPower();

        expect($opticalPower)->toBeArray()->toHaveCount(3);
        expect($opticalPower[0])->toBeFloat();
        expect($opticalPower[1])->toBeFloat();
        expect($opticalPower[2])->toBeFloat();
    });
})->skipIfFakeConnection();

describe('optical power with onts', function () {
    it('can get multiple ZTE-C300', function () {
        Ont::create([
            'name' => 'ALCLB407BB97',
            'interface' => 'gpon-onu_1/2/1:66',
            'cto_id' => 1,
        ]);
        
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

        $opticalPower = Onmt::onts($onts)->opticalPower();

        expect($opticalPower)->toBeArray()->toHaveCount(3);
        expect($opticalPower[0])->toBeFloat();
        expect($opticalPower[1])->toBeFloat();
        expect($opticalPower[2])->toBeFloat();
    });

    it('can get multiple ZTE-C600', function () {
        Dio::create([
            'name' => 'dio-test2',
            'olt_id' => 2,
        ]);
    
        Ceo::create([
            'name' => 'BB01-CX01',
            'dio_id' => 2,
        ]);
    
        CeoSplitter::create([
            'name' => 'FTTH-101',
            'type' => '1x8',
            'slot' => 1,
            'pon' => 1,
            'ceo_id' => 2,
        ]);
    
        Cto::create([
            'name' => 'SP01',
            'type' => '1x8',
            'ceo_splitter_id' => 2,
        ]);  

        Ont::create([
            'name' => 'CMSZ3B112D31',
            'interface' => 'gpon_onu-1/1/1:5',
            'cto_id' => 2,
        ]);
        
        Ont::create([
            'name' => 'CMSZ3B112C41',
            'interface' => 'gpon_onu-1/1/1:4',
            'cto_id' => 2,
        ]);

        Ont::create([
            'name' => 'ZTEGCF2603E6',
            'interface' => 'gpon_onu-1/1/1:7',
            'cto_id' => 2,
        ]);

        $onts = Ont::all();

        $opticalPower = Onmt::onts($onts)->opticalPower();

        expect($opticalPower)->toBeArray()->toHaveCount(3);
        expect($opticalPower[0])->toBeFloat();
        expect($opticalPower[1])->toBeFloat();
        expect($opticalPower[2])->toBeFloat();
    });

    it('can get multiple Nokia-FX16', function () {
        Dio::create([
            'name' => 'dio-test3',
            'olt_id' => 3,
        ]);
    
        Ceo::create([
            'name' => 'BB01-CX01',
            'dio_id' => 2,
        ]);
    
        CeoSplitter::create([
            'name' => 'FTTH-101',
            'type' => '1x8',
            'slot' => 1,
            'pon' => 1,
            'ceo_id' => 2,
        ]);
    
        Cto::create([
            'name' => 'SP01',
            'type' => '1x8',
            'ceo_splitter_id' => 2,
        ]);  

        Ont::create([
            'name' => 'ALCLFC5A84A7',
            'interface' => '1/1/1/1/8',
            'cto_id' => 2,
        ]);
        
        Ont::create([
            'name' => 'ALCLFC5ABE9F',
            'interface' => '1/1/1/1/9',
            'cto_id' => 2,
        ]);

        Ont::create([
            'name' => 'ALCLB40D3FF9',
            'interface' => '1/1/1/1/5',
            'cto_id' => 2,
        ]);

        $onts = Ont::all();

        $opticalPower = Onmt::onts($onts)->opticalPower();

        expect($opticalPower)->toBeArray()->toHaveCount(3);
        expect($opticalPower[0])->toBeFloat();
        expect($opticalPower[1])->toBeFloat();
        expect($opticalPower[2])->toBeFloat();
    });

    it('can get multiple Fiberhome-AN551604', function () {
        Dio::create([
            'name' => 'dio-test2',
            'olt_id' => 4,
        ]);
    
        Ceo::create([
            'name' => 'BB01-CX01',
            'dio_id' => 2,
        ]);
    
        CeoSplitter::create([
            'name' => 'FTTH-101',
            'type' => '1x8',
            'slot' => 1,
            'pon' => 1,
            'ceo_id' => 2,
        ]);
    
        Cto::create([
            'name' => 'SP01',
            'type' => '1x8',
            'ceo_splitter_id' => 2,
        ]);  

        Ont::create([
            'name' => 'CMSZ3B1128B1',
            'interface' => 'NA-NA-12-1',
            'cto_id' => 2,
        ]);
        
        Ont::create([
            'name' => 'CMSZ3B0BBCA2',
            'interface' => 'NA-NA-12-1',
            'cto_id' => 2,
        ]);

        Ont::create([
            'name' => 'ALCLB40D3604',
            'interface' => 'NA-NA-12-1',
            'cto_id' => 2,
        ]);

        $onts = Ont::all();

        $opticalPower = Onmt::onts($onts)->opticalPower();

        expect($opticalPower)->toBeArray()->toHaveCount(3);
        expect($opticalPower[0])->toBeFloat();
        expect($opticalPower[1])->toBeFloat();
        expect($opticalPower[2])->toBeFloat();
    });
})->skipIfFakeConnection();
