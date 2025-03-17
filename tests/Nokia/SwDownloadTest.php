<?php

use Illuminate\Support\Collection;
use PauloHortelan\Onmt\Facades\Nokia;
use PauloHortelan\Onmt\Models\CommandResultBatch;

uses()->group('Nokia');

beforeEach(function () {
    $ipServer = env('NOKIA_OLT_IP');
    $username = env('NOKIA_OLT_USERNAME_TELNET');
    $password = env('NOKIA_OLT_PASSWORD_TELNET');

    $this->interfaceALCL = env('NOKIA_INTERFACE_ALCL');

    $this->serialALCL = env('NOKIA_SERIAL_ALCL');
    $this->serialCMSZ = env('NOKIA_SERIAL_CMSZ');

    $this->nokia = Nokia::connectTelnet($ipServer, $username, $password, 23);
});

describe('Nokia Optical Interface', function () {

    it('can get ont interface detail', function () {
        $this->nokia->interfaces(['1/1/1/1/3']);

        $downloadDetails = $this->nokia->swDownloadDetailOnts();

        expect($downloadDetails)->toBeInstanceOf(Collection::class);

        $downloadDetails->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    })->only();
});

afterAll(function () {
    $this->nokia->disconnect();
});
