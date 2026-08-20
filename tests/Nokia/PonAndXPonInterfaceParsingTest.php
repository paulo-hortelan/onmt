<?php

use PauloHortelan\Onmt\Services\Connections\Telnet;
use PauloHortelan\Onmt\Services\Nokia\NokiaService;

describe('Nokia PON and X-PON combined ONT listing', function () {
    beforeEach(function () {
        $reflection = new ReflectionClass(NokiaService::class);

        $databaseTransactionsDisabled = $reflection->getProperty('databaseTransactionsDisabled');
        $databaseTransactionsDisabled->setAccessible(true);
        $databaseTransactionsDisabled->setValue(null, true);

        $model = $reflection->getProperty('model');
        $model->setAccessible(true);
        $model->setValue(null, 'FX16');

        $operator = $reflection->getProperty('operator');
        $operator->setAccessible(true);
        $operator->setValue(null, 'test');

        $ponOutput = <<<'OUT'
typ:isadmin># show equipment ont status pon 1/1/1/1 detail
============================================================================================================================================================================================================================================
pon table (detailed)
============================================================================================================================================================================================================================================

--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
pon
--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
                   pon : 1/1/1/1                                  ont : 1/1/1/1/1                             sernum : ALCL:FE12FC43                   admin-status : up                               oper-status : up
 olt-rx-sig-level(dbm) : -18.1                   ont-olt-distance(km) : 0.2                                    desc1 : gpon-ont
                 desc2 :
              hostname : undefined
============================================================================================================================================================================================================================================
OUT;

        $xPonOutput = <<<'OUT'
typ:isadmin># show equipment ont status x-pon 1/1/1/1 detail
============================================================================================================================================================================================================================================
x-pon table (detailed)
============================================================================================================================================================================================================================================

--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
x-pon
--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
                 x-pon : 1/1/1/1                                  ont : 1/1/1/1/3                             sernum : ZTEG:DA17F119                   admin-status : up                               oper-status : up
 olt-rx-sig-level(dbm) : -19.9                   ont-olt-distance(km) : 0.1                                    desc1 : treinamento2
                 desc2 :
              hostname : undefined
============================================================================================================================================================================================================================================
OUT;

        $mockTelnet = $this->createMock(Telnet::class);
        $mockTelnet->method('exec')->willReturnCallback(function (string $command) use ($ponOutput, $xPonOutput) {
            if (str_contains($command, 'x-pon')) {
                return $xPonOutput;
            }

            return $ponOutput;
        });

        $telnetProperty = $reflection->getProperty('telnetConn');
        $telnetProperty->setAccessible(true);
        $telnetProperty->setValue(null, $mockTelnet);

        $this->nokia = (new NokiaService)->disableDatabaseTransactions()->model('FX16');
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

    it('concatenates pon and x-pon ont results in a single batch', function () {
        $batches = $this->nokia->ontsByPonAndXPonInterface('1/1/1/1');
        $batch = $batches->first();

        expect($batch->allCommandsSuccessful())->toBeTrue();
        expect($batch->getCommands())->toHaveCount(2);
        expect($batch->getCommands()[0]->result[0]['interface'])->toBe('1/1/1/1/1');
        expect($batch->getCommands()[1]->result[0]['interface'])->toBe('1/1/1/1/3');
    });

    it('uses combined pon and x-pon indexes to find the next ont index', function () {
        expect($this->nokia->getNextOntIndex('1/1/1/1'))->toBe(2);
    });
});
