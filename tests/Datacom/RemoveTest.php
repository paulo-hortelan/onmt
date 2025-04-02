<?php

use Illuminate\Support\Collection;
use PauloHortelan\Onmt\Facades\Datacom;

uses()->group('Datacom');

beforeEach(function () {
    $this->ipServer = env('DATACOM_DM4612_OLT_IP');
    $this->usernameTelnet = env('DATACOM_DM4612_OLT_USERNAME_TELNET');
    $this->passwordTelnet = env('DATACOM_DM4612_OLT_PASSWORD_TELNET');

    $this->serial = env('DATACOM_DM4612_SERIAL_CMSZ');
    $this->ponInterface = env('DATACOM_DM4612_PON_INTERFACE_CMSZ');
    $this->interface = env('DATACOM_DM4612_INTERFACE_CMSZ');
});

describe('Datacom Remove', function () {
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

    it('can remove ONT', function () {
        $interface = $this->interface;

        $this->datacom->interfaces([$interface]);

        $result = $this->datacom->removeOnts();

        dump($result->toArray());

        expect($result)->toBeInstanceOf(Collection::class);

        $commandResultBatch = $result->first();

        expect($commandResultBatch->allCommandsSuccessful())->toBeTrue();
    });

    it('can remove Service Port', function () {
        $interface = $this->interface;

        $this->datacom->interfaces([$interface]);

        $result = $this->datacom->ontsServicePort();

        $commandResultBatch = $result->first();

        expect($commandResultBatch->allCommandsSuccessful())->toBeTrue();

        $command = $commandResultBatch->commands->last();

        dump($command->toArray());

        if (empty($command->result)) {
            throw new Exception('No service port found');
        }

        $port = $command->result[0]['servicePortId'];

        $result = $this->datacom->removeServicePorts([$port]);

        $commandResultBatch = $result->first();

        expect($commandResultBatch->allCommandsSuccessful())->toBeTrue();
    });

    it('can do complete removal', function () {
        $interface = $this->interface;

        $this->datacom->startRecordingCommands(
            description: 'Remove ONTs',
            interface: $interface,
        );

        $this->datacom->interfaces([$interface]);

        $result = $this->datacom->ontsServicePortByInterfaces();

        $commandResultBatch = $result->first();

        expect($commandResultBatch->allCommandsSuccessful())->toBeTrue();

        $command = $commandResultBatch->commands->last();

        $port = $command->result[0]['servicePortId'];

        $result = $this->datacom->removeServicePorts([$port]);

        expect($result->first()->allCommandsSuccessful())->toBeTrue();

        $result = $this->datacom->removeOnts();

        expect($result->first()->allCommandsSuccessful())->toBeTrue();

        $commandBatchResult = $this->datacom->stopRecordingCommands();

        expect($commandBatchResult->first()->allCommandsSuccessful())->toBeTrue();
    })->only();
});
