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

    $this->ponInterface = env('FIBERHOME_PON_INTERFACE');

    $this->fiberhome = Fiberhome::connectTL1($ipOlt, $username, $password, 3337, $ipServer);
});

describe('Fiberhome Unregistered Onts - Success', function () {
    it('can get unregistered onts', function () {
        $unregisteredOnts = $this->fiberhome->unregisteredOnts($this->ponInterface);

        expect($unregisteredOnts)->toBeInstanceOf(Collection::class);

        $unregisteredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });
});
