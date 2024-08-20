<?php

use PauloHortelan\Onmt\Facades\Fiberhome;

uses()->group('Fiberhome');

beforeEach(function () {
    $ipOlt = env('FIBERHOME_OLT_IP');
    $ipServer = env('FIBERHOME_IP_SERVER');
    $username = env('FIBERHOME_OLT_USERNAME');
    $password = env('FIBERHOME_OLT_PASSWORD');

    $this->serial1 = env('FIBERHOME_SERIAL_1');
    $this->serial2 = env('FIBERHOME_SERIAL_2');
    $this->serial3 = env('FIBERHOME_SERIAL_3');

    $this->interface1 = env('FIBERHOME_INTERFACE_1');
    $this->interface2 = env('FIBERHOME_INTERFACE_2');
    $this->interface3 = env('FIBERHOME_INTERFACE_3');

    $this->fiberhome = Fiberhome::connect($ipOlt, $username, $password, 3337, $ipServer);
});

describe('Fiberhome Ont Optical Power - Success', function () {
    it('can get single power', function () {
        $this->fiberhome->interfaces([$this->interface1])->serials([$this->serial1]);

        $powers = $this->fiberhome->ontsOpticalPower();

        expect($powers)->toBeArray();
        expect($powers[0]['result']['rxPower'])->toBeFloat();
    });

    it('can get multiple powers', function () {
        $this->fiberhome->interfaces([$this->interface1, $this->interface2, $this->interface3]);
        $this->fiberhome->serials([$this->serial1, $this->serial2, $this->serial3]);

        $powers = $this->fiberhome->ontsOpticalPower();

        expect($powers)->toBeArray();
        expect($powers[0]['result']['rxPower'])->toBeFloat();
        expect($powers[1]['result']['rxPower'])->toBeFloat();
        expect($powers[2]['result']['rxPower'])->toBeFloat();
    });
});
