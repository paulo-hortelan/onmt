<?php

use PauloHortelan\Onmt\Services\Connections\Telnet;
use PauloHortelan\Onmt\Services\Nokia\Models\FX16;
use PauloHortelan\Onmt\Services\Nokia\NokiaService;

describe('Nokia X-PON ONT status parsing', function () {
    beforeEach(function () {
        $reflection = new ReflectionClass(NokiaService::class);

        $databaseTransactionsDisabled = $reflection->getProperty('databaseTransactionsDisabled');
        $databaseTransactionsDisabled->setAccessible(true);
        $databaseTransactionsDisabled->setValue(null, true);

        $mockTelnet = $this->createMock(Telnet::class);
        $mockTelnet->method('exec')->willReturn(<<<'OUT'
typ:isadmin># show equipment ont status x-pon 1/1/1/1 detail
============================================================================================================================================================================================================================================
x-pon table (detailed)
============================================================================================================================================================================================================================================

--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
x-pon
--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
                 x-pon : 1/1/1/1                                  ont : 1/1/1/1/1                             sernum : ZTEG:DA17F119                   admin-status : up                               oper-status : up                   
 olt-rx-sig-level(dbm) : -19.9                   ont-olt-distance(km) : 0.1                                    desc1 : treinamento2                                                        
                 desc2 :                                                                     
              hostname : undefined                                                                                                                                                                                                         
============================================================================================================================================================================================================================================
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

    it('parses detailed x-pon ont status fields', function () {
        $result = FX16::showEquipmentOntStatusXPon('1/1/1/1');

        expect($result->success)->toBeTrue();
        expect($result->command)->toBe('show equipment ont status x-pon 1/1/1/1 detail');
        expect($result->result)->toHaveCount(1);
        expect($result->result[0])->toMatchArray([
            'pon-interface' => '1/1/1/1',
            'interface' => '1/1/1/1/1',
            'sernum' => 'ZTEG:DA17F119',
            'admin-status' => 'up',
            'oper-status' => 'up',
            'olt-rx-sig-level' => -19.9,
            'ont-olt-distance' => 0.1,
            'desc1' => 'treinamento2',
            'desc2' => null,
            'hostname' => 'undefined',
        ]);
    });
});
