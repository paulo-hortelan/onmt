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

    $this->serialALCLC300 = env('ZTE_C300_SERIAL_ALCL');
    $this->serialCMSZC300 = env('ZTE_C300_SERIAL_CMSZ');
    $this->serialALCLC600 = env('ZTE_C600_SERIAL_ALCL');
    $this->serialCMSZC600 = env('ZTE_C600_SERIAL_CMSZ');

    $this->interfaceALCLC300 = env('ZTE_C300_INTERFACE_ALCL');
    $this->interfaceCMSZC300 = env('ZTE_C300_INTERFACE_CMSZ');
    $this->interfaceALCLC600 = env('ZTE_C600_INTERFACE_ALCL');
    $this->interfaceCMSZC600 = env('ZTE_C600_INTERFACE_CMSZ');
});

describe('ZTE C300 - Ont Optical Power', function () {
    it('can get optical power', function () {
        $zte = ZTE::connectTelnet($this->ipServerC300, $this->usernameTelnetC300, $this->passwordTelnetC300, 23);

        $zte->interfaces([$this->interfaceCMSZC300]);

        $result = $zte->ontsOpticalPower();

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
                expect($commandResult->result['up-olt-rx'])->toBeFloat();
            });
        });
    });
})->only();

describe('ZTE C600 - Ont Optical Power', function () {
    it('can get optical power', function () {
        $zte = ZTE::connectTelnet($this->ipServerC600, $this->usernameTelnetC600, $this->passwordTelnetC600, 23, null, 'C600');

        $zte->interfaces([$this->interfaceCMSZC600]);

        $ontsPower = $zte->ontsOpticalPower();

        expect($ontsPower)->toBeInstanceOf(Collection::class);

        $ontsPower->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
                expect($commandResult->result['up-olt-rx'])->toBeFloat();
            });
        });
    });
});
