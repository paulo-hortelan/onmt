<?php

use Illuminate\Support\Collection;
use PauloHortelan\Onmt\Facades\Nokia;
use PauloHortelan\Onmt\Models\CommandResultBatch;

uses()->group('Nokia');

beforeEach(function () {
    $ipOlt = env('NOKIA_OLT_IP');
    $username = env('NOKIA_OLT_USERNAME_TELNET');
    $password = env('NOKIA_OLT_PASSWORD_TELNET');

    $this->serialALCL = env('NOKIA_SERIAL_ALCL');
    $this->serialCMSZ = env('NOKIA_SERIAL_CMSZ');

    $this->interfaceALCL = env('NOKIA_INTERFACE_ALCL');
    $this->interfaceCMSZ = env('NOKIA_INTERFACE_CMSZ');

    $this->ponInterfaceALCL = env('NOKIA_PON_INTERFACE_ALCL');

    $this->nokia = Nokia::connectTelnet($ipOlt, $username, $password, 23);
});

describe('Nokia Onts by Pon Interface - Success', function () {
    it('can get onts', function () {
        $onts = $this->nokia->ontsByPonInterface($this->ponInterfaceALCL);

        expect($onts)->toBeInstanceOf(Collection::class);

        $onts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeArray();

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult['success'])->toBeTrue();
            });
        });
    });
});
