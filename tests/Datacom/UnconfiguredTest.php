<?php

use Illuminate\Support\Collection;
use PauloHortelan\Onmt\Facades\Datacom;
use PauloHortelan\Onmt\Models\CommandResultBatch;

uses()->group('Datacom');

beforeEach(function () {
    $this->ipServer = env('DATACOM_DM4612_OLT_IP');
    $this->usernameTelnet = env('DATACOM_DM4612_OLT_USERNAME_TELNET');
    $this->passwordTelnet = env('DATACOM_DM4612_OLT_PASSWORD_TELNET');
});

describe('Datacom - Unconfigured Onts - Success', function () {
    it('can get unconfigured onts', function () {
        $datacom = Datacom::connectTelnet($this->ipServer, $this->usernameTelnet, $this->passwordTelnet, 23);

        $unconfiguredOnts = $datacom->unconfiguredOnts();

        expect($unconfiguredOnts)->toBeInstanceOf(Collection::class);

        $unconfiguredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });
});
