<?php

use Illuminate\Support\Collection;
use PauloHortelan\Onmt\Models\CommandResult;
use PauloHortelan\Onmt\Models\CommandResultBatch;
use PauloHortelan\Onmt\Services\Connections\Telnet;
use PauloHortelan\Onmt\Services\ZTE\Models\C300;
use PauloHortelan\Onmt\Services\ZTE\Models\C600;
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

    it('can execute command to detailOntsInfo', function () {
        $zteservice = new ZTEService();
        $interface = '1/1/1:1';

        $zteservice->interfaces([$interface]);

        $result = $zteservice->detailOntsInfo();

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
        expect($result->first()->commands->first()->command)->toBe("show pon power attenuation gpon-onu_$interface");
    });

    it('accepts the C300 description echo with terminal control characters', function () {
        $description = 'hotspotpracavillaflora.12';
        $command = "description $description";
        $response = $command
            .str_repeat("\x08", strlen($command))
            ."\$ $description"
            .str_repeat("\x08", strlen("\$ $description"));

        $reflection = new ReflectionClass(ZTEService::class);
        $telnetProperty = $reflection->getProperty('telnetConn');
        $telnetProperty->setAccessible(true);

        $mockTelnet = Mockery::mock(Telnet::class);
        $mockTelnet->shouldReceive('exec')
            ->once()
            ->with($command)
            ->andReturn($response);
        $telnetProperty->setValue(null, $mockTelnet);

        $result = C300::description($description);

        expect($result)
            ->toBeInstanceOf(CommandResult::class)
            ->and($result?->getAttribute('success'))->toBeTrue()
            ->and($result?->getAttribute('error'))->toBeNull()
            ->and($result?->getAttribute('response'))->toBe($response);
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

    it('accepts the C600 description echo with terminal control characters', function () {
        $description = 'hotspotpracavillaflora.12';
        $command = "description $description";
        $response = $command
            .str_repeat("\x08", strlen($command))
            ."\$ $description"
            .str_repeat("\x08", strlen("\$ $description"));

        $reflection = new ReflectionClass(ZTEService::class);
        $telnetProperty = $reflection->getProperty('telnetConn');
        $telnetProperty->setAccessible(true);

        $mockTelnet = Mockery::mock(Telnet::class);
        $mockTelnet->shouldReceive('exec')
            ->once()
            ->with($command)
            ->andReturn($response);
        $telnetProperty->setValue(null, $mockTelnet);

        $result = C600::description($description);

        expect($result)
            ->toBeInstanceOf(CommandResult::class)
            ->and($result?->getAttribute('success'))->toBeTrue()
            ->and($result?->getAttribute('error'))->toBeNull()
            ->and($result?->getAttribute('response'))->toBe($response);
    });
});
