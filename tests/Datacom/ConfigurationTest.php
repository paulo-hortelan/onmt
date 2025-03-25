<?php

use PauloHortelan\Onmt\Facades\Datacom;

uses()->group('Datacom');

beforeEach(function () {
    $this->ipServer = env('DATACOM_DM4612_OLT_IP');
    $this->usernameTelnet = env('DATACOM_DM4612_OLT_USERNAME_TELNET');
    $this->passwordTelnet = env('DATACOM_DM4612_OLT_PASSWORD_TELNET');

    $this->serialALCL = env('DATACOM_DM4612_SERIAL_ALCL');
    $this->ponInterfaceALCL = env('DATACOM_DM4612_PON_INTERFACE_ALCL');
    $this->interfaceALCL = env('DATACOM_DM4612_INTERFACE_ALCL');

    $this->serialCMSZ = env('DATACOM_DM4612_SERIAL_CMSZ');
    $this->ponInterfaceCMSZ = env('DATACOM_DM4612_PON_INTERFACE_CMSZ');
    $this->interfaceCMSZ = env('DATACOM_DM4612_INTERFACE_CMSZ');
});

describe('Datacom Router Configurations', function () {
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

    it('can set veip', function () {
        $interface = $this->interfaceALCL;
        $veipPort = 1;

        $this->datacom->interfaces([$interface]);

        $result = $this->datacom->setVeip($veipPort);

        dump($result->toArray());

        expect($result)->not->toBeEmpty();

        $commandBatch = $result->first();

        expect($commandBatch->allCommandsSuccessful())->toBeTrue();

        $lastCommand = $commandBatch->commands->last();
        expect($lastCommand->command)->toContain('veip');
        expect($lastCommand->success)->toBe(true);
    });

    it('can set service port', function () {
        $ponInterface = $this->ponInterfaceALCL;

        $port = $this->datacom->getNextServicePort($ponInterface);

        dump($port);

        expect($port)->toBeInt();

        $interface = $this->interfaceALCL;
        $vlan = 110;
        $description = 'teste_onu4';

        $this->datacom->interfaces([$interface]);

        $result = $this->datacom->setServicePort($port, $vlan, $description);

        dump($result->toArray());

        expect($result)->not->toBeEmpty();

        $commandBatch = $result->first();

        expect($commandBatch->allCommandsSuccessful())->toBeTrue();

        $lastCommand = $commandBatch->commands->last();
        expect($lastCommand->command)->toContain('service-port');
        expect($lastCommand->success)->toBe(true);
    });

    it('can do complete configuration', function () {
        $vlan = 110;
        $interface = $this->interfaceALCL;
        $ponInterface = $this->ponInterfaceALCL;
        $description = 'teste_onu4';

        $this->datacom->startRecordingCommands(
            description: 'Configuration ONT Router',
            interface: $interface,
            serial: $this->serialALCL
        );

        $this->datacom->interfaces([$interface]);

        $result = $this->datacom->setVeip(1);

        expect($result->first()->allCommandsSuccessful())->toBeTrue();

        $port = $this->datacom->getNextServicePort($ponInterface);

        expect($port)->toBeInt();

        $result = $this->datacom->setServicePort($port, $vlan, $description);

        expect($result->first()->allCommandsSuccessful())->toBeTrue();

        $result = $this->datacom->commitConfigurations();

        expect($result->first()->allCommandsSuccessful())->toBeTrue();

        $commandBatchResult = $this->datacom->stopRecordingCommands();

        dump($commandBatchResult->toArray());

        expect($commandBatchResult->first()->allCommandsSuccessful())->toBeTrue();
    });
});

describe('Datacom Bridge Configurations', function () {
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

    it('can set ethernet negotiation', function () {
        $ethernetPort = 1;
        $interface = $this->interfaceCMSZ;

        $this->datacom->interfaces([$interface]);

        $result = $this->datacom->setNegotiation($ethernetPort);

        dump($result->toArray());

        expect($result)->not->toBeEmpty();

        $commandBatch = $result->first();

        expect($commandBatch->allCommandsSuccessful())->toBeTrue();

        $lastCommand = $commandBatch->commands->last();
        expect($lastCommand->command)->toContain('negotiation');
        expect($lastCommand->success)->toBe(true);
    });

    it('can set ethernet not shutdown', function () {
        $ethernetPort = 1;
        $interface = $this->interfaceCMSZ;

        $this->datacom->interfaces([$interface]);

        $result = $this->datacom->setNoShutdown($ethernetPort);

        dump($result->toArray());

        expect($result)->not->toBeEmpty();

        $commandBatch = $result->first();

        expect($commandBatch->allCommandsSuccessful())->toBeTrue();

        $lastCommand = $commandBatch->commands->last();
        expect($lastCommand->command)->toContain('no shutdown');
        expect($lastCommand->success)->toBe(true);
    });

    it('can set ethernet native vlan', function () {
        $ethernetPort = 1;
        $vlan = 110;
        $interface = $this->interfaceCMSZ;

        $this->datacom->interfaces([$interface]);

        $result = $this->datacom->setNativeVlan($ethernetPort, $vlan);

        dump($result->toArray());

        expect($result)->not->toBeEmpty();

        $commandBatch = $result->first();

        expect($commandBatch->allCommandsSuccessful())->toBeTrue();

        $lastCommand = $commandBatch->commands->last();
        expect($lastCommand->command)->toContain('native vlan');
        expect($lastCommand->success)->toBe(true);
    });

    it('can set service port', function () {
        $ponInterface = $this->ponInterfaceCMSZ;

        $port = $this->datacom->getNextServicePort($ponInterface);

        dump($port);

        expect($port)->toBeInt();

        $interface = $this->interfaceCMSZ;
        $vlan = 110;
        $description = 'teste_onu3';

        $this->datacom->interfaces([$interface]);

        $result = $this->datacom->setServicePort($port, $vlan, $description);

        dump($result->toArray());

        expect($result)->not->toBeEmpty();

        $commandBatch = $result->first();

        expect($commandBatch->allCommandsSuccessful())->toBeTrue();

        $lastCommand = $commandBatch->commands->last();
        expect($lastCommand->command)->toContain('service-port');
        expect($lastCommand->success)->toBe(true);
    });

    it('can do complete configuration', function () {
        $interface = $this->interfaceCMSZ;
        $ponInterface = $this->ponInterfaceCMSZ;
        $ethernetPort = 1;
        $vlan = 110;
        $description = 'teste_onu3';

        $this->datacom->startRecordingCommands(
            description: 'Configuration ONT Bridge',
            interface: $interface,
            serial: $this->serialCMSZ
        );
        $this->datacom->interfaces([$interface]);

        $result = $this->datacom->setNegotiation($ethernetPort);

        expect($result->first()->allCommandsSuccessful())->toBeTrue();

        $result = $this->datacom->setNoShutdown($ethernetPort);

        expect($result->first()->allCommandsSuccessful())->toBeTrue();

        $result = $this->datacom->setNativeVlan($ethernetPort, $vlan);

        expect($result->first()->allCommandsSuccessful())->toBeTrue();

        $port = $this->datacom->getNextServicePort($ponInterface);

        expect($port)->toBeInt();

        $result = $this->datacom->setServicePort($port, $vlan, $description);

        expect($result->first()->allCommandsSuccessful())->toBeTrue();

        $result = $this->datacom->commitConfigurations();

        expect($result->first()->allCommandsSuccessful())->toBeTrue();

        $commandBatchResult = $this->datacom->stopRecordingCommands();

        dump($commandBatchResult->toArray());

        expect($commandBatchResult->first()->allCommandsSuccessful())->toBeTrue();
    })->only();
});
