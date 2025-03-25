<?php

use PauloHortelan\Onmt\Facades\Datacom;

uses()->group('Datacom');

beforeEach(function () {
    $this->ipServer = env('DATACOM_DM4612_OLT_IP');
    $this->usernameTelnet = env('DATACOM_DM4612_OLT_USERNAME_TELNET');
    $this->passwordTelnet = env('DATACOM_DM4612_OLT_PASSWORD_TELNET');

    $this->serial = env('DATACOM_DM4612_SERIAL_ALCL');
    $this->ponInterface = env('DATACOM_DM4612_PON_INTERFACE_ALCL');
    $this->interface = env('DATACOM_DM4612_INTERFACE_ALCL');
});

describe('Datacom Authorization', function () {
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

    it('can set ONT name', function () {
        $interface = $this->interface;

        $result = $this->datacom->interfaces([$interface])->setName('teste_onu4');

        expect($result)->not->toBeEmpty();

        $commandBatch = $result->first();

        expect($commandBatch->allCommandsSuccessful())->toBeTrue();

        $lastCommand = $commandBatch->commands->last();
        expect($lastCommand->command)->toContain('name');
        expect($lastCommand->success)->toBe(true);
    });

    it('can set ONT serial-number', function () {
        $interface = $this->interface;

        $result = $this->datacom->interfaces([$interface])->setSerialNumber($this->serial);

        expect($result)->not->toBeEmpty();

        $commandBatch = $result->first();

        expect($commandBatch->allCommandsSuccessful())->toBeTrue();

        $lastCommand = $commandBatch->commands->last();
        expect($lastCommand->command)->toContain('serial-number');
        expect($lastCommand->success)->toBe(true);
    });

    it('can set ONT line profile', function () {
        $interface = $this->interface;
        $lineProfile = 'PPPoE-Router';

        $result = $this->datacom->interfaces([$interface])->setLineProfile($lineProfile);

        expect($result)->not->toBeEmpty();

        $commandBatch = $result->first();

        expect($commandBatch->allCommandsSuccessful())->toBeTrue();

        $lastCommand = $commandBatch->commands->last();
        expect($lastCommand->command)->toContain('line-profile');
        expect($lastCommand->success)->toBe(true);
    });

    it('can set ONT SNMP profile', function () {
        $interface = $this->interface;
        $profile = 'SNMP';

        $result = $this->datacom->interfaces([$interface])->setSnmpProfile($profile);

        expect($result)->not->toBeEmpty();

        $commandBatch = $result->first();

        expect($commandBatch->allCommandsSuccessful())->toBeTrue();

        $lastCommand = $commandBatch->commands->last();
        expect($lastCommand->command)->toContain('snmp profile');
        expect($lastCommand->success)->toBe(true);
    });

    it('can set ONT SNMP real time', function () {
        $interface = $this->interface;

        $result = $this->datacom->interfaces([$interface])->setSnmpRealTime();

        expect($result)->not->toBeEmpty();

        $commandBatch = $result->first();

        expect($commandBatch->allCommandsSuccessful())->toBeTrue();

        $lastCommand = $commandBatch->commands->last();
        expect($lastCommand->command)->toContain('snmp real-time');
        expect($lastCommand->success)->toBe(true);
    });

    it('can do complete authorization', function () {
        $ponInterface = $this->ponInterface;

        $this->datacom->startRecordingCommands(
            description: 'Authorize ONT',
            ponInterface: $ponInterface,
            serial: $this->serial
        );

        $ontIndex = $this->datacom->getNextOntIndex($ponInterface);

        $interface = $ponInterface.'/'.$ontIndex;

        $this->datacom->interfaces([$interface]);

        $result = $this->datacom->setName('teste_onu4');

        expect($result->first()->allCommandsSuccessful())->toBeTrue();

        $result = $this->datacom->setSerialNumber($this->serial);

        expect($result->first()->allCommandsSuccessful())->toBeTrue();

        $lineProfile = 'PPPoE-Bridge';
        $result = $this->datacom->setLineProfile($lineProfile);

        expect($result->first()->allCommandsSuccessful())->toBeTrue();

        $snmpProfile = 'SNMP';
        $result = $this->datacom->setSnmpProfile($snmpProfile);

        expect($result->first()->allCommandsSuccessful())->toBeTrue();

        $result = $this->datacom->setSnmpRealTime();

        expect($result->first()->allCommandsSuccessful())->toBeTrue();

        $result = $this->datacom->commitConfigurations();

        expect($result->first()->allCommandsSuccessful())->toBeTrue();

        $commandBatchResult = $this->datacom->stopRecordingCommands();

        dump($commandBatchResult->toArray());

        expect($commandBatchResult->first()->allCommandsSuccessful())->toBeTrue();
    })->only();
});
