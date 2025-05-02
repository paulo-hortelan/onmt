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

    $this->ponInterface = env('FIBERHOME_PON_INTERFACE');

    $this->fiberhome = Fiberhome::connectTL1($ipOlt, $username, $password, 3337, $ipServer);
});

describe('Fiberhome Ont Optical Power', function () {
    it('can get single power', function () {
        $this->fiberhome->serials(['CMSZ3B0A590F']);

        $this->fiberhome->disableDatabaseTransactions();

        $powers = $this->fiberhome->ontsOpticalPower('NA-NA-14-6');

        dump($powers->toArray());

        expect($powers)->toBeInstanceOf(Collection::class);

        $powers->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
                expect($commandResult->result['RxPower'])->toBeFloat();
            });
        });
    })->only();

    it('can get multiple powers', function () {
        $this->fiberhome->serials([$this->serialALCL, $this->serialCMSZ]);

        $powers = $this->fiberhome->ontsOpticalPower($this->ponInterface);

        expect($powers)->toBeInstanceOf(Collection::class);

        $powers->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
                expect($commandResult->result['rxPower'])->toBeFloat();
            });
        });
    });
});
