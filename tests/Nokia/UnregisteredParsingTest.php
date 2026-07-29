<?php

use PauloHortelan\Onmt\Services\Connections\Telnet;
use PauloHortelan\Onmt\Services\Nokia\Models\FX16;
use PauloHortelan\Onmt\Services\Nokia\NokiaService;

describe('Nokia unregistered ONT parsing', function () {
    beforeEach(function () {
        $reflection = new ReflectionClass(NokiaService::class);

        $databaseTransactionsDisabled = $reflection->getProperty('databaseTransactionsDisabled');
        $databaseTransactionsDisabled->setAccessible(true);
        $databaseTransactionsDisabled->setValue(null, true);

        $mockTelnet = $this->createMock(Telnet::class);
        $mockTelnet->method('exec')->willReturn(<<<'OUT'
show pon unprovision-onu
==================================================================================
unprovision-onu table
==================================================================================
         |                 |              |                |              |actual 
alarm-idx|gpon-index       |sernum        |subscriber-locid|logical-authid|us-rate
++++
 5         1/1/1/16          ALCLB483BA51   DEFAULT                         1.25g  
23        x-pon:1/1/1/1     ALCLFD638BD2   DEFAULT                         10g    
24        1/1/1/1           ALCLFCF53A88   DEFAULT                         1.25g  

unprovision-onu count : 3
==================================================================================
OUT);

        $telnetProperty = $reflection->getProperty('telnetConn');
        $telnetProperty->setAccessible(true);
        $telnetProperty->setValue(null, $mockTelnet);
    });

    afterEach(function () {
        $reflection = new ReflectionClass(NokiaService::class);

        $telnetProperty = $reflection->getProperty('telnetConn');
        $telnetProperty->setAccessible(true);
        $telnetProperty->setValue(null, null);

        $databaseTransactionsDisabled = $reflection->getProperty('databaseTransactionsDisabled');
        $databaseTransactionsDisabled->setAccessible(true);
        $databaseTransactionsDisabled->setValue(null, false);
    });

    it('extracts actual us rate from unprovisioned ONT rows', function () {
        $result = FX16::showPonUnprovisionOnu();

        expect($result->success)->toBeTrue();
        expect($result->result)->toHaveCount(3);
        expect($result->result[0])->toMatchArray([
            'alarm-idx' => 5,
            'interface' => '1/1/1/16',
            'serial' => 'ALCLB483BA51',
            'actual-us-rate' => '1.25g',
        ]);
        expect($result->result[1])->toMatchArray([
            'alarm-idx' => 23,
            'interface' => 'x-pon:1/1/1/1',
            'serial' => 'ALCLFD638BD2',
            'actual-us-rate' => '10g',
        ]);
    });
});
