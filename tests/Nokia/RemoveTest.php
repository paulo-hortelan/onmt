<?php

use Illuminate\Support\Collection;
use PauloHortelan\Onmt\Facades\Nokia;
use PauloHortelan\Onmt\Models\CommandResultBatch;

uses()->group('Nokia');

beforeEach(function () {
    $this->ipOlt = env('NOKIA_OLT_IP');

    $this->usernameTelnet = env('NOKIA_OLT_USERNAME_TELNET');
    $this->passwordTelnet = env('NOKIA_OLT_PASSWORD_TELNET');
    $this->usernameTL1 = env('NOKIA_OLT_USERNAME_TL1');
    $this->passwordTL1 = env('NOKIA_OLT_PASSWORD_TL1');

    $this->serialALCL = env('NOKIA_SERIAL_ALCL');
    $this->serialCMSZ = env('NOKIA_SERIAL_CMSZ');

    $this->interfaceALCL = env('NOKIA_INTERFACE_ALCL');

    $this->pppoeUsername = env('NOKIA_PPPOE_USERNAME');
    $this->pppoePassword = env('NOKIA_PPPOE_PASSWORD');

    $this->ponInterface = env('NOKIA_PON_INTERFACE');
});

describe('Nokia Remove ONTs', function () {
    it('can remove onts', function () {
        $this->nokiaTelnet = Nokia::connectTelnet($this->ipOlt, $this->usernameTelnet, $this->passwordTelnet, 23);

        $this->nokiaTelnet->interfaces(['1/1/1/1/3']);

        $removedOnts = $this->nokiaTelnet->removeOnts();

        expect($removedOnts)->toBeInstanceOf(Collection::class);

        $removedOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });
});
