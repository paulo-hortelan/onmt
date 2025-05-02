<?php

use Illuminate\Support\Collection;
use PauloHortelan\Onmt\Facades\Nokia;
use PauloHortelan\Onmt\Models\CommandResultBatch;

uses()->group('Nokia');

beforeEach(function () {
    $this->ipOlt = env('NOKIA_OLT_IP');

    $this->usernameTelnet = env('NOKIA_OLT_USERNAME_TELNET');
    $this->passwordTelnet = env('NOKIA_OLT_PASSWORD_TELNET');
    $this->usernameTL1 = env('NOKIA_OLT_USERNAME_TL1');
    $this->passwordTL1 = env('NOKIA_OLT_PASSWORD_TL1');

    $this->serialALCL = env('NOKIA_SERIAL_ALCL');
    $this->serialCMSZ = env('NOKIA_SERIAL_CMSZ');

    $this->interfaceALCL = env('NOKIA_INTERFACE_ALCL');

    $this->pppoeUsername = env('NOKIA_PPPOE_USERNAME');
    $this->pppoePassword = env('NOKIA_PPPOE_PASSWORD');

    $this->ponInterface = env('NOKIA_PON_INTERFACE');
});

describe('Nokia Configure PPPOE and VLAN on ONTs - Router Nokia', function () {
    it('can configure vlan', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/15']);

        $result = $this->nokiaTL1->configureTr069Vlan(110, 1);

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure pppoe username and password', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $configuredOnts = $this->nokiaTL1->configureTr069Pppoe('teste_onu_mk2', '1234', 2, 3);

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });
});

describe('Nokia Configure WIFI on ONTs - Router Nokia', function () {
    it('can configure 2.4Ghz', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $configuredOnts = $this->nokiaTL1->configureTr069Wifi2_4Ghz('Wifi-Nokia-2.4Ghz', '12345678', 4, 5);

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure 5Ghz', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $configuredOnts = $this->nokiaTL1->configureTr069Wifi5Ghz('Wifi-Nokia-5Ghz', '12345678', 6, 7);

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });
});

describe('Nokia Configure Account on ONTs - Router Nokia', function () {
    it('can configure webaccount password', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $configuredOnts = $this->nokiaTL1->configureTr069WebAccountPassword('ALC#FGU', 8);

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure account password', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $configuredOnts = $this->nokiaTL1->configureTr069AccountPassword('nokia123', 9);

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });
});

describe('Nokia Configure DNS on ONTs - Router Nokia', function () {
    it('can configure all dns\'s password', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $configuredOnts = $this->nokiaTL1->configureTr069DNS('186.224.0.18\,186.224.0.20', 12, 13, 14);

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });
});
