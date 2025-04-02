<?php

use PauloHortelan\Onmt\Facades\Datacom;

uses()->group('Datacom');

beforeEach(function () {
    $this->ipServer = env('DATACOM_DM4612_OLT_IP');
    $this->usernameTelnet = env('DATACOM_DM4612_OLT_USERNAME_TELNET');
    $this->passwordTelnet = env('DATACOM_DM4612_OLT_PASSWORD_TELNET');

    $this->ponInterfaceALCL = env('DATACOM_DM4612_PON_INTERFACE_ALCL');
    $this->interfaceALCL = env('DATACOM_DM4612_INTERFACE_ALCL');
});

describe('Datacom Terminal Mode', function () {
    beforeEach(function () {
        $this->datacom = Datacom::connectTelnet(
            $this->ipServer,
            $this->usernameTelnet,
            $this->passwordTelnet,
            23
        );
    });

    afterEach(function () {
        $this->datacom->disconnect();
    });

    it('can enter config terminal mode', function () {
        $result = $this->datacom->setConfigTerminalMode();

        expect($result->allCommandsSuccessful())->toBeTrue();

        $commands = $result->commands;
        expect($commands)->toHaveCount(1);
        expect($commands[0]->command)->toBe('config');
        expect($commands[0]->success)->toBeTrue();
    });

    it('can enter interface gpon terminal mode', function () {
        $ponInterface = $this->ponInterfaceALCL;

        $result = $this->datacom->setInterfaceGponTerminalMode($ponInterface);

        expect($result->allCommandsSuccessful())->toBeTrue();

        $commands = $result->commands;
        expect($commands)->toHaveCount(2); // config + interface gpon commands

        $interfaceCommand = $commands->last();
        expect($interfaceCommand->command)->toBe("interface gpon $ponInterface");
        expect($interfaceCommand->success)->toBeTrue();
    });

    it('automatically enters config mode before interface gpon mode', function () {
        $reflectionClass = new ReflectionClass($this->datacom);
        $property = $reflectionClass->getProperty('terminalMode');
        $property->setAccessible(true);
        $property->setValue($this->datacom, '');

        $ponInterface = $this->ponInterfaceALCL;

        $result = $this->datacom->setInterfaceGponTerminalMode($ponInterface);

        expect($result->allCommandsSuccessful())->toBeTrue();

        $commands = $result->commands;
        expect($commands)->toHaveCount(2);

        expect($commands[0]->command)->toBe('config');

        expect($commands[1]->command)->toBe("interface gpon $ponInterface");
    });

    it('can enter onu terminal mode', function () {
        $fullInterface = $this->interfaceALCL;

        $result = $this->datacom->setOnuTerminalMode($fullInterface);

        expect($result->allCommandsSuccessful())->toBeTrue();

        $commands = $result->commands;
        expect($commands)->toHaveCount(3);

        $onuCommand = $commands->last();
        expect($onuCommand->command)->toContain('onu ');
        expect($onuCommand->success)->toBeTrue();
    });

    it('automatically enters required modes before onu mode', function () {
        $reflectionClass = new ReflectionClass($this->datacom);
        $property = $reflectionClass->getProperty('terminalMode');
        $property->setAccessible(true);
        $property->setValue($this->datacom, '');

        $fullInterface = $this->interfaceALCL;
        $ontIndex = (new \PauloHortelan\Onmt\Services\Datacom\DatacomService())->getOntIndexFromInterface($fullInterface);

        $result = $this->datacom->setOnuTerminalMode($fullInterface);

        expect($result->allCommandsSuccessful())->toBeTrue();

        $commands = $result->commands;
        expect($commands)->toHaveCount(3);

        expect($commands[0]->command)->toBe('config');
        expect($commands[1]->command)->toContain('interface gpon ');
        expect($commands[2]->command)->toBe("onu $ontIndex");

        $terminalMode = $property->getValue($this->datacom);
        expect($terminalMode)->toBe("onu-$ontIndex");
    });

    it('directly enters onu mode when already in interface gpon mode', function () {
        $fullInterface = $this->interfaceALCL;
        $datacomService = new \PauloHortelan\Onmt\Services\Datacom\DatacomService();
        $ponInterface = $datacomService->getPonInterfaceFromInterface($fullInterface);
        $ontIndex = $datacomService->getOntIndexFromInterface($fullInterface);

        $this->datacom->setInterfaceGponTerminalMode($ponInterface);

        $reflectionClass = new ReflectionClass($this->datacom);
        $property = $reflectionClass->getProperty('terminalMode');
        $property->setAccessible(true);

        $terminalMode = $property->getValue($this->datacom);
        expect($terminalMode)->toBe("interface-gpon-$ponInterface");

        $result = $this->datacom->setOnuTerminalMode($fullInterface);

        expect($result->allCommandsSuccessful())->toBeTrue();

        $commands = $result->commands;
        expect($commands)->toHaveCount(1);

        expect($commands[0]->command)->toBe("onu $ontIndex");
    });
});
