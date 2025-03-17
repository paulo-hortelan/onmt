<?php

use Illuminate\Support\Collection;
use PauloHortelan\Onmt\Facades\Nokia;
use PauloHortelan\Onmt\Models\CommandResultBatch;

uses()->group('Nokia');

beforeEach(function () {
    $this->ipServer = env('NOKIA_OLT_IP');
    $this->username = env('NOKIA_OLT_USERNAME_TELNET');
    $this->password = env('NOKIA_OLT_PASSWORD_TELNET');

    $this->serialALCL = env('NOKIA_SERIAL_ALCL');
    $this->serialCMSZ = env('NOKIA_SERIAL_CMSZ');

    $this->interfaceALCL = env('NOKIA_INTERFACE_ALCL');
    $this->interfaceCMSZ = env('NOKIA_INTERFACE_CMSZ');
});

describe('Nokia Optical Detail - Success', function () {
    it('can get single detail', function () {
        $nokia = Nokia::connectTelnet($this->ipServer, $this->username, $this->password, 23);

        $nokia->interfaces([$this->interfaceALCL]);

        $detailOnts = $nokia->detailOnts();

        expect($detailOnts)->toBeInstanceOf(Collection::class);

        $detailOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
                expect($commandResult->result['rx-signal-level'])->toBeFloat();
            });
        });
    })->only();

    it('can get multiple details', function () {
        $nokia = Nokia::connectTelnet($this->ipServer, $this->username, $this->password, 23);

        $nokia->interfaces([$this->interfaceALCL, $this->interfaceCMSZ]);

        $detailOnts = $nokia->detailOnts();

        expect($detailOnts)->toBeInstanceOf(Collection::class);

        $detailOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
                expect($commandResult->result['rx-signal-level'])->toBeFloat();
            });
        });
    });
});

describe('Nokia Optical Detail By Serial - Success', function () {
    it('can get single detail', function () {
        $nokia = Nokia::connectTelnet($this->ipServer, $this->username, $this->password, 23);

        $nokia->serials([$this->serialALCL]);

        $detailOnts = $nokia->detailOntsBySerials();

        expect($detailOnts)->toBeInstanceOf(Collection::class);

        $detailOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can get multiple details', function () {
        $nokia = Nokia::connectTelnet($this->ipServer, $this->username, $this->password, 23);

        $nokia->serials([$this->serialALCL, $this->serialCMSZ]);

        $detailOnts = $nokia->detailOntsBySerials();

        expect($detailOnts)->toBeInstanceOf(Collection::class);

        $detailOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });
});
