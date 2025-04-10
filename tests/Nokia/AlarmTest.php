<?php

use Illuminate\Support\Collection;
use PauloHortelan\Onmt\Facades\Nokia;
use PauloHortelan\Onmt\Models\CommandResultBatch;

uses()->group('Nokia');

beforeEach(function () {
    $this->ipServer = env('NOKIA_OLT_IP');
    $this->username = env('NOKIA_OLT_USERNAME_TELNET');
    $this->password = env('NOKIA_OLT_PASSWORD_TELNET');

    $this->interfaceALCL = env('NOKIA_INTERFACE_ALCL');
    $this->interfaceCMSZ = env('NOKIA_INTERFACE_CMSZ');
});

describe('Nokia Alarm - Success', function () {
    it('can get alarm', function () {
        $nokia = Nokia::connectTelnet($this->ipServer, $this->username, $this->password, 23);

        $nokia->interfaces(['1/1/1/12/4']);

        $result = $nokia->alarmOnts();

        dump($result->toArray());

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
                expect($commandResult->result['servaff'])->toBeString();
            });
        });
    })->only();
});
