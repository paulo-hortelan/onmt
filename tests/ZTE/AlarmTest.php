<?php

use Illuminate\Support\Collection;
use PauloHortelan\Onmt\Facades\ZTE;
use PauloHortelan\Onmt\Models\CommandResultBatch;

uses()->group('ZTE');

beforeEach(function () {
    $this->ipServerC300 = env('ZTE_C300_OLT_IP');
    $this->usernameTelnetC300 = env('ZTE_C300_OLT_USERNAME_TELNET');
    $this->passwordTelnetC300 = env('ZTE_C300_OLT_PASSWORD_TELNET');
    $this->ipServerC600 = env('ZTE_C600_OLT_IP');
    $this->usernameTelnetC600 = env('ZTE_C600_OLT_USERNAME_TELNET');
    $this->passwordTelnetC600 = env('ZTE_C600_OLT_PASSWORD_TELNET');

    $this->interfaceALCLC300 = env('ZTE_C300_INTERFACE_ALCL');
    $this->interfaceCMSZC300 = env('ZTE_C300_INTERFACE_CMSZ');
    $this->interfaceALCLC600 = env('ZTE_C600_INTERFACE_ALCL');
    $this->interfaceCMSZC600 = env('ZTE_C600_INTERFACE_CMSZ');

    $this->ponInterfaceALCLC300 = env('ZTE_C300_PON_INTERFACE_ALCL');
    $this->ponInterfaceCMSZC300 = env('ZTE_C300_PON_INTERFACE_CMSZ');
    $this->ponInterfaceALCLC600 = env('ZTE_C600_PON_INTERFACE_ALCL');
    $this->ponInterfaceCMSZC600 = env('ZTE_C600_PON_INTERFACE_CMSZ');
});

describe('ZTE C300 - Alarms', function () {
    it('can get alarm', function () {
        $zte = ZTE::connectTelnet($this->ipServerC300, $this->usernameTelnetC300, $this->passwordTelnetC300, 23);

        $zte->interfaces(['1/2/1:39']);

        $result = $zte->alarmOnts();

        dump($result->toArray());

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
                expect($commandResult->result['alarm-history'])->toBeArray();
            });
        });
    })->only();
});

describe('ZTE C600 - Alarms', function () {
    it('can get alarm', function () {
        $zte = ZTE::connectTelnet($this->ipServerC600, $this->usernameTelnetC600, $this->passwordTelnetC600, 23, model: 'C600');

        $zte->interfaces(['1/2/1:39']);

        $result = $zte->alarmOnts();

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
                expect($commandResult->result['alarm-history'])->toBeArray();
            });
        });
    })->only();
});
