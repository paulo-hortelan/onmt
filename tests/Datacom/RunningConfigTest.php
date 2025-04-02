<?php

use Illuminate\Support\Collection;
use PauloHortelan\Onmt\Facades\Datacom;
use PauloHortelan\Onmt\Models\CommandResultBatch;

uses()->group('Datacom');

beforeEach(function () {
    $this->ipServer = env('DATACOM_DM4612_OLT_IP');
    $this->usernameTelnet = env('DATACOM_DM4612_OLT_USERNAME_TELNET');
    $this->passwordTelnet = env('DATACOM_DM4612_OLT_PASSWORD_TELNET');

    $this->ponInterface = env('DATACOM_DM4612_PON_INTERFACE_CMSZ');
    $this->interface = env('DATACOM_DM4612_INTERFACE_CMSZ');
});

describe('Datacom Running Config', function () {
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

    it('can get ont service port', function () {
        $this->datacom->interfaces(['1/1/4/1']);

        $this->datacom->setOnu();
        $result = $this->datacom->ontsServicePort();

        dump($result->toArray());

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();

            });
        });
    });

    it('can get onts service port by pon interface', function () {
        $ponInterface = $this->ponInterface;

        $result = $this->datacom->ontsServicePortByPonInterface($ponInterface);

        dump($result->toArray());

        expect($result->allCommandsSuccessful())->toBeTrue();

        $commands = $result->commands;
        expect($commands[0]->success)->toBeTrue();
    });

    it('can get next ont service port', function () {
        $ponInterface = $this->ponInterface;

        $nextServicePort = $this->datacom->getNextServicePort();

        dump($nextServicePort);

        expect($nextServicePort)->toBeInt();
    })->only();
});
