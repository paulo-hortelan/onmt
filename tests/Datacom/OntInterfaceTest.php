<?php

use Illuminate\Support\Collection;
use PauloHortelan\Onmt\Facades\Datacom;
use PauloHortelan\Onmt\Models\CommandResultBatch;

uses()->group('Datacom');

beforeEach(function () {
    $this->ipServer = env('DATACOM_DM4612_OLT_IP');
    $this->usernameTelnet = env('DATACOM_DM4612_OLT_USERNAME_TELNET');
    $this->passwordTelnet = env('DATACOM_DM4612_OLT_PASSWORD_TELNET');

    $this->serialALCL = env('DATACOM_DM4612_SERIAL_ALCL');
});

describe('Datacom - Onts Interface', function () {
    it('can find onu by serial number successfully', function () {
        $datacom = Datacom::connectTelnet($this->ipServer, $this->usernameTelnet, $this->passwordTelnet, 23);

        $datacom->serials([$this->serialALCL]);

        $result = $datacom->interfaceOnts();

        expect($result)
            ->toBeInstanceOf(Collection::class)
            ->not->toBeEmpty();

        $result->each(function ($batch) {
            expect($batch)
                ->toBeInstanceOf(CommandResultBatch::class)
                ->and($batch->commands)->toBeInstanceOf(Collection::class)
                ->and($batch->commands)->not->toBeEmpty();

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();

                expect($commandResult->result[0])->toHaveKeys([
                    'interface',
                    'onuId',
                    'serialNumber',
                    'operState',
                    'softwareDownloadState',
                    'name',
                ]);
            });
        });
    });
});
