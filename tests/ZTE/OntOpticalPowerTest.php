<?php

use PauloHortelan\Onmt\Facades\ZTE;

uses()->group('ZTE');

beforeEach(function () {
    $ipServer = env('ZTE_OLT_IP');
    $username = env('ZTE_OLT_USERNAME');
    $password = env('ZTE_OLT_PASSWORD');

    $this->serial1 = env('ZTE_SERIAL_1');
    $this->serial2 = env('ZTE_SERIAL_2');
    $this->serial3 = env('ZTE_SERIAL_3');

    $this->interface1 = env('ZTE_INTERFACE_1');
    $this->interface2 = env('ZTE_INTERFACE_2');
    $this->interface3 = env('ZTE_INTERFACE_3');

    $this->zte = ZTE::connect($ipServer, $username, $password);
});

describe('ZTE Ont Optical Power - Success', function () {
    it('can get single power', function () {
        $powers = $this->zte->ontsOpticalPower([$this->interface1]);

        expect($powers)->toBeArray();
        expect($powers[0]['result']['downRxPower'])->toBeFloat();

        $powers = $this->zte->interface($this->interface1)->ontsOpticalPower();

        expect($powers)->toBeArray();
        expect($powers[0]['result']['downRxPower'])->toBeFloat();
    });

    it('can get multiple powers', function () {
        $interfaces = [$this->interface1, $this->interface2, $this->interface3];

        $powers = $this->zte->ontsOpticalPower($interfaces);

        expect($powers)->toBeArray();
        expect($powers[0]['result']['downRxPower'])->toBeFloat();
        expect($powers[1]['result']['downRxPower'])->toBeFloat();
        expect($powers[2]['result']['downRxPower'])->toBeFloat();

        $powers = $this->zte->interfaces($interfaces)->ontsOpticalPower();

        expect($powers)->toBeArray();
        expect($powers[0]['result']['downRxPower'])->toBeFloat();
        expect($powers[1]['result']['downRxPower'])->toBeFloat();
        expect($powers[2]['result']['downRxPower'])->toBeFloat();
    });
});

describe('ZTE Ont Optical Power By Serial - Success', function () {
    it('can get single power', function () {
        $powers = $this->zte->ontsOpticalPowerBySerial([$this->serial1]);

        expect($powers)->toBeArray();
        expect($powers[0]['result']['downRxPower'])->toBeFloat();

        $powers = $this->zte->serial($this->serial1)->ontsOpticalPowerBySerial();

        expect($powers)->toBeArray();
        expect($powers[0]['result']['downRxPower'])->toBeFloat();
    });

    it('can get multiple power', function () {
        $serials = [$this->serial1, $this->serial2, $this->serial3];

        $powers = $this->zte->ontsOpticalPowerBySerial($serials);

        expect($powers)->toBeArray();
        expect($powers[0]['result']['downRxPower'])->toBeFloat();
        expect($powers[1]['result']['downRxPower'])->toBeFloat();
        expect($powers[2]['result']['downRxPower'])->toBeFloat();

        $powers = $this->zte->serials($serials)->ontsOpticalPowerBySerial();

        expect($powers)->toBeArray();
        expect($powers[0]['result']['downRxPower'])->toBeFloat();
        expect($powers[1]['result']['downRxPower'])->toBeFloat();
        expect($powers[2]['result']['downRxPower'])->toBeFloat();

        $serials = [$this->serial1, $this->serial2, 'ALCLFC000000'];

        $powers = $this->zte->ontsOpticalPowerBySerial($serials);

        expect($powers)->toBeArray();
        expect($powers[0]['result']['downRxPower'])->toBeFloat();
        expect($powers[1]['result']['downRxPower'])->toBeFloat();
        expect($powers[2]['success'])->toBeFalse();

        $powers = $this->zte->serials($serials)->ontsOpticalPowerBySerial();

        expect($powers)->toBeArray();
        expect($powers[0]['result']['downRxPower'])->toBeFloat();
        expect($powers[1]['result']['downRxPower'])->toBeFloat();
        expect($powers[2]['success'])->toBeFalse();
    });
});

afterAll(function () {
    $this->zte->disconnect();
});