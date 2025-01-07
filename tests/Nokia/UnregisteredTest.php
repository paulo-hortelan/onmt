<?php

use Illuminate\Support\Collection;
use PauloHortelan\Onmt\Facades\Nokia;
use PauloHortelan\Onmt\Models\CommandResultBatch;

uses()->group('Nokia');

beforeEach(function () {
    $ipOlt = env('NOKIA_OLT_IP');
    $username = env('NOKIA_OLT_USERNAME_TELNET');
    $password = env('NOKIA_OLT_PASSWORD_TELNET');

    $this->nokia = Nokia::connectTelnet($ipOlt, $username, $password, 23);
});

describe('Nokia Unregistered Onts - Success', function () {
    it('can get unregistered onts', function () {
        $unregisteredOnts = $this->nokia->unregisteredOnts();

        expect($unregisteredOnts)->toBeInstanceOf(Collection::class);

        $tests = CommandResultBatch::all();

        $unregisteredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });
});
