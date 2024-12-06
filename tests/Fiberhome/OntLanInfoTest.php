<?php

use PauloHortelan\Onmt\Facades\Fiberhome;

uses()->group('Fiberhome');

beforeEach(function () {
    $ipOlt = env('FIBERHOME_OLT_IP');
    $ipServer = env('FIBERHOME_IP_SERVER');
    $username = env('FIBERHOME_OLT_USERNAME');
    $password = env('FIBERHOME_OLT_PASSWORD');

    $this->serial1 = env('FIBERHOME_SERIAL_ALCL');
    $this->serial2 = env('FIBERHOME_SERIAL_2');
    $this->serial3 = env('FIBERHOME_SERIAL_3');

    $this->interface1 = env('FIBERHOME_INTERFACE_ALCL');
    $this->interface2 = env('FIBERHOME_INTERFACE_2');
    $this->interface3 = env('FIBERHOME_INTERFACE_3');

    $this->fiberhome = Fiberhome::connect($ipOlt, $username, $password, 3337, $ipServer);
});

describe('Fiberhome Optical Lan Info - Success', function () {
    it('can get single lan info', function () {
        $this->fiberhome->interfaces([$this->interface1])->serials([$this->serial1]);
        $lans = $this->fiberhome->ontsLanInfo();

        expect($lans)->toBeArray();
        expect($lans[0]['success'])->toBeTrue();
        expect($lans[0]['result']['adminStatus'])->toBeString();
    });

    it('can get multiple lan infos', function () {
        $this->fiberhome->interfaces([$this->interface1, $this->interface2, $this->interface3]);
        $this->fiberhome->serials([$this->serial1, $this->serial2, $this->serial3]);

        $lans = $this->fiberhome->ontsLanInfo();

        expect($lans)->toBeArray();
        expect($lans[0]['success'])->toBeTrue();
        expect($lans[0]['result']['adminStatus'])->toBeString();
        expect($lans[1]['success'])->toBeTrue();
        expect($lans[1]['result']['adminStatus'])->toBeString();
        expect($lans[2]['success'])->toBeTrue();
        expect($lans[2]['result']['adminStatus'])->toBeString();
    });
});
