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

    $this->ponInterfaceALCLC600 = env('ZTE_C600_PON_INTERFACE_ALCL');
    $this->ponInterfaceCMSZC600 = env('ZTE_C600_PON_INTERFACE_CMSZ');
});

describe('ZTE C300 - Onts by pon interface', function () {
    it('can get onts by pon interface', function () {
        $zte = ZTE::connectTelnet($this->ipServerC300, $this->usernameTelnetC300, $this->passwordTelnetC300, 23);

        $onts = $zte->ontsByPonInterface($this->ponInterfaceALCLC300);

        dump($onts->toArray());

        expect($onts)->toBeInstanceOf(Collection::class);

        $onts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can get the next free ont index', function () {
        $zte = ZTE::connectTelnet($this->ipServerC300, $this->usernameTelnetC300, $this->passwordTelnetC300, 23);

        $ontIndex = $zte->getNextOntIndex($this->ponInterfaceALCLC300);

        expect($ontIndex)->toBeInt();
        expect($ontIndex)->toBeGreaterThan(0);
    });
});

describe('ZTE C600 - Onts by pon interface', function () {
    it('can get onts by pon interface', function () {
        $zte = ZTE::connectTelnet($this->ipServerC600, $this->usernameTelnetC600, $this->passwordTelnetC600, 23, null, 'C600');

        $onts = $zte->ontsByPonInterface($this->ponInterfaceALCLC600);

        dump($onts->toArray());

        expect($onts)->toBeInstanceOf(Collection::class);

        $onts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can get the next free ont index', function () {
        $zte = ZTE::connectTelnet($this->ipServerC600, $this->usernameTelnetC600, $this->passwordTelnetC600, 23, null, 'C600');

        $ontIndex = $zte->getNextOntIndex($this->ponInterfaceALCLC600);

        expect($ontIndex)->toBeInt();
        expect($ontIndex)->toBeGreaterThan(0);
    });
});
