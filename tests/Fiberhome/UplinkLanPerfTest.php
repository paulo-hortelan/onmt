<?php

use Illuminate\Support\Collection;
use PauloHortelan\Onmt\Facades\Fiberhome;
use PauloHortelan\Onmt\Models\CommandResultBatch;

uses()->group('Fiberhome');

beforeEach(function () {
    $ipOlt = env('FIBERHOME_OLT_IP');
    $ipServer = env('FIBERHOME_IP_SERVER');
    $username = env('FIBERHOME_OLT_USERNAME_TL1');
    $password = env('FIBERHOME_OLT_PASSWORD_TL1');

    $this->serialALCL = env('FIBERHOME_SERIAL_ALCL');
    $this->serialCMSZ = env('FIBERHOME_SERIAL_CMSZ');

    $this->interfaceALCL = env('FIBERHOME_INTERFACE_ALCL');
    $this->interfaceCMSZ = env('FIBERHOME_INTERFACE_CMSZ');

    $this->fiberhome = Fiberhome::connect($ipOlt, $username, $password, 3337, $ipServer);
});

describe('Fiberhome OLT Optical Lan Perf', function () {
    it('can get single lan info', function () {
        $this->fiberhome->interfaces([$this->interfaceALCL])->serials([$this->serialALCL]);
        $portInterface = 'NA-NA-1-1';

        $lanPerfs = $this->fiberhome->oltUplinksLanPerf($portInterface);

        expect($lanPerfs)->toBeInstanceOf(Collection::class);

        $lanPerfs->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });
});
