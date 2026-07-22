<?php

use PauloHortelan\Onmt\Services\Connections\Telnet;
use PauloHortelan\Onmt\Services\Nokia\Models\FX16;
use PauloHortelan\Onmt\Services\Nokia\NokiaService;

describe('Nokia ONT interface detail parsing', function () {
    beforeEach(function () {
        $reflection = new ReflectionClass(NokiaService::class);

        $databaseTransactionsDisabled = $reflection->getProperty('databaseTransactionsDisabled');
        $databaseTransactionsDisabled->setAccessible(true);
        $databaseTransactionsDisabled->setValue(null, true);

        $mockTelnet = $this->createMock(Telnet::class);
        $mockTelnet->method('exec')->willReturn(<<<'OUT'
typ:isadmin># show equipment ont interface 1/1/1/16/3 detail
============================================================================================================================================================================================================================================
interface table (detailed)
============================================================================================================================================================================================================================================

--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
interface
--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
               ont-idx : 1/1/1/16/3                      eqpt-ver-num : 3TN00384BDAA01                    sw-ver-act : 3TN00383IJMI13                    sw-ver-psv : 3TN00383JJLK24                     vendor-id : ALCL                 
              equip-id : "     00000G1426GA"         actual-num-slots : 0                             version-number : 3TN00384BDAA01                    num-tconts : 32                             num-trf-sched : 32                   
       num-prio-queues : 256                      auto-sw-planned-ver : 3TN00383IJMI13          auto-sw-download-ver : DISABLED                              sernum : ALCL:FE12FC43        
          yp-serial-no : ALCLfe12fc43                                                                                                                                                                                                     
         oper-spec-ver : unknown                         act-ont-type : hgu                         act-txpower-ctrl : tx-rx                       sn-bundle-status : idle                        cfgfile1-ver-act : PREALCL056           
      cfgfile1-ver-psv :                             cfgfile2-ver-act :                             cfgfile2-ver-psv :                               actual-us-rate : 1.25g                          template-name : DEFAULT              
      auto-prov-status : not-applicable        
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

    it('normalizes quoted padded equip-id values', function () {
        $result = FX16::showEquipmentOntInterface('1/1/1/16/3');

        expect($result->success)->toBeTrue();
        expect($result->result['equip-id'])->toBe('00000G1426GA');
        expect($result->result['actual-num-slots'])->toBe(0);
        expect($result->result['num-prio-queues'])->toBe(256);
    });
});
