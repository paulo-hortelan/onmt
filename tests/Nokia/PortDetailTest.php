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
    $this->interfaceCMSZ = env('NOKIA_INTERFACE_CMSZ');

    $this->nokia = Nokia::connectTelnet($ipServer, $username, $password, 23);
});

describe('Nokia Port Detail', function () {
    it('can get single detail', function () {
        $this->nokia->interfaces([$this->interfaceALCL]);

        $details = $this->nokia->portDetailOnts();

        expect($details)->toBeInstanceOf(Collection::class);

        $details->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    })->only();

    it('can get multiple details', function () {
        $this->nokia->interfaces([$this->interfaceALCL, $this->interfaceCMSZ]);

        $details = $this->nokia->portDetailOnts();

        $details->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });
});
