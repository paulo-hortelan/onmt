<?php

use PauloHortelan\Onmt\Services\Connections\Telnet;
use PauloHortelan\Onmt\Services\Datacom\DatacomService;

uses()->group('Datacom');

beforeEach(function () {
    $reflection = new ReflectionClass(DatacomService::class);

    $databaseTransactionsDisabled = $reflection->getProperty('databaseTransactionsDisabled');
    $databaseTransactionsDisabled->setAccessible(true);
    $databaseTransactionsDisabled->setValue(null, true);

    $terminalMode = $reflection->getProperty('terminalMode');
    $terminalMode->setAccessible(true);
    $terminalMode->setValue(null, '');

    $ipOlt = $reflection->getProperty('ipOlt');
    $ipOlt->setAccessible(true);
    $ipOlt->setValue(null, '127.0.0.1');

    $model = $reflection->getProperty('model');
    $model->setAccessible(true);
    $model->setValue(null, 'DM4612');

    $operator = $reflection->getProperty('operator');
    $operator->setAccessible(true);
    $operator->setValue(null, 'test');

    $mockTelnet = $this->createMock(Telnet::class);
    $mockTelnet->method('exec')->willReturnCallback(function (string $command) {
        return match ($command) {
            'config' => "config\nEntering configuration mode terminal",
            'service-port new' => 'service-port new',
            'gpon 1/1/1 onu 2 gem 2 match vlan vlan-id 7 action vlan replace vlan-id 7 description pppoe' => 'gpon 1/1/1 onu 2 gem 2 match vlan vlan-id 7 action vlan replace vlan-id 7 description pppoe',
            'top' => 'top',
            'commit' => 'commit',
            default => throw new RuntimeException("Unexpected command: $command"),
        };
    });
    $mockTelnet->method('changePromptRegex');
    $mockTelnet->method('resetPromptRegex');

    $telnetProperty = $reflection->getProperty('telnetConn');
    $telnetProperty->setAccessible(true);
    $telnetProperty->setValue(null, $mockTelnet);
});

afterEach(function () {
    $reflection = new ReflectionClass(DatacomService::class);

    foreach ([
        'telnetConn' => null,
        'terminalMode' => '',
        'model' => '',
        'operator' => null,
        'ipOlt' => '',
        'databaseTransactionsDisabled' => false,
    ] as $propertyName => $value) {
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue(null, $value);
    }

    DatacomService::$interfaces = [];
    DatacomService::$serials = [];
});

it('runs service-port new in submode and returns to config before commit', function () {
    $datacom = (new DatacomService())
        ->disableDatabaseTransactions()
        ->interfaces(['1/1/1/2']);

    $result = $datacom->setServicePortNew(7, 'pppoe', 2);

    expect($result)->not->toBeEmpty();
    expect($result->first()->allCommandsSuccessful())->toBeTrue();
    expect($result->first()->commands->pluck('command')->all())->toBe([
        'config',
        'service-port new',
        'gpon 1/1/1 onu 2 gem 2 match vlan vlan-id 7 action vlan replace vlan-id 7 description pppoe',
    ]);

    $reflection = new ReflectionClass(DatacomService::class);
    $terminalMode = $reflection->getProperty('terminalMode');
    $terminalMode->setAccessible(true);
    expect($terminalMode->getValue())->toBe('service-port-new');

    $commitResult = $datacom->commitConfigurations();

    expect($commitResult)->not->toBeEmpty();
    expect($commitResult->first()->allCommandsSuccessful())->toBeTrue();
    expect($commitResult->first()->commands->pluck('command')->all())->toBe([
        'top',
        'commit',
    ]);
    expect($terminalMode->getValue())->toBe('config');
});
