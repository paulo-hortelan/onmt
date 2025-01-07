<?php

use Illuminate\Support\Collection;
use PauloHortelan\Onmt\Models\CommandResult;
use PauloHortelan\Onmt\Models\CommandResultBatch;
use PauloHortelan\Onmt\Services\Connections\Telnet;
use PauloHortelan\Onmt\Services\ZTE\ZTEService;

describe('ZTE C300', function () {
    beforeEach(function () {
        $reflection = new ReflectionClass(ZTEService::class);
        $modelProperty = $reflection->getProperty('model');
        $modelProperty->setAccessible(true);
        $modelProperty->setValue(null, 'C300');

        $mockTelnet = $this->createMock(Telnet::class);

        $mockTelnet->method('exec')
            ->willReturn(true);

        $telnetProperty = $reflection->getProperty('telnetConn');
        $telnetProperty->setAccessible(true);
        $telnetProperty->setValue(null, $mockTelnet);
    });

    it('can execute command to disableTerminalLength', function () {
        $zteservice = new ZTEService();
        $result = $zteservice->disableTerminalLength();

        expect($result)->toBeInstanceOf(CommandResult::class);
        expect($result->command)->toBe('terminal length 0');
    });

    it('can execute command to ontsDetailInfo', function () {
        $zteservice = new ZTEService();
        $interface = '1/1/1:1';

        $zteservice->interfaces([$interface]);

        $result = $zteservice->ontsDetailInfo();

        expect($result)->toBeInstanceOf(Collection::class);
        expect($result->first())->toBeInstanceOf(CommandResultBatch::class);
        expect($result->first()->commands->first()->command)->toBe("show gpon onu detail-info gpon-onu_$interface");
    });

    it('can execute command to ontsOpticalPower', function () {
        $zteservice = new ZTEService();
        $interface = '1/1/1:1';

        $zteservice->interfaces([$interface]);

        $result = $zteservice->ontsOpticalPower();

        expect($result)->toBeInstanceOf(Collection::class);
        expect($result->first())->toBeInstanceOf(CommandResultBatch::class);
        expect($result->first()->commands->first()->command)->toBe("show gpon onu detail-info gpon-onu_$interface");
    });
});

describe('ZTE C600', function () {
    beforeEach(function () {
        $reflection = new ReflectionClass(ZTEService::class);
        $modelProperty = $reflection->getProperty('model');
        $modelProperty->setAccessible(true);
        $modelProperty->setValue(null, 'C300');

        $mockTelnet = $this->createMock(Telnet::class);

        $mockTelnet->method('exec')
            ->willReturn(true);

        $telnetProperty = $reflection->getProperty('telnetConn');
        $telnetProperty->setAccessible(true);
        $telnetProperty->setValue(null, $mockTelnet);
    });

    it('can execute command to disableTerminalLength', function () {
        $zteservice = new ZTEService();
        $result = $zteservice->disableTerminalLength();

        expect($result)->toBeInstanceOf(CommandResult::class);
        expect($result->command)->toBe('terminal length 0');
    });
});
