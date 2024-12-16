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

    $this->fiberhome = Fiberhome::connectTL1($ipOlt, $username, $password, 3337, $ipServer);
});

describe('Fiberhome Optical Lan Info - Success', function () {
    it('can get single lan info', function () {
        $this->fiberhome->interfaces([$this->interfaceALCL])->serials([$this->serialALCL]);

        $lans = $this->fiberhome->ontsLanInfo();

        expect($lans)->toBeInstanceOf(Collection::class);

        $lans->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can get multiple lan infos', function () {
        $this->fiberhome->interfaces([$this->interfaceALCL, $this->interfaceCMSZ]);
        $this->fiberhome->serials([$this->serialALCL, $this->serialCMSZ]);

        $lans = $this->fiberhome->ontsLanInfo();

        expect($lans)->toBeInstanceOf(Collection::class);

        $lans->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });
});
