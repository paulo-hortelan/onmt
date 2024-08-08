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

    $this->fiberhome = Fiberhome::connect($ipOlt, $username, $password, $ipServer);
});

describe('Fiberhome Onts State Info - Success', function () {
    it('can get single info', function () {
        $this->fiberhome->interfaces([$this->interface1])->serials([$this->serial1]);
        $states = $this->fiberhome->ontsStateInfo();

        expect($states)->toBeArray();
        expect($states[0]['success'])->toBeTrue();
        expect($states[0]['result']['adminState'])->toBeString();
    });

    it('can get multiple infos', function () {
        $this->fiberhome->interfaces([$this->interface1, $this->interface2, $this->interface3]);
        $this->fiberhome->serials([$this->serial1, $this->serial2, $this->serial3]);

        $states = $this->fiberhome->ontsStateInfo();

        expect($states)->toBeArray();
        expect($states[0]['success'])->toBeTrue();
        expect($states[0]['result']['adminState'])->toBeString();
        expect($states[1]['success'])->toBeTrue();
        expect($states[1]['result']['adminState'])->toBeString();
        expect($states[2]['success'])->toBeTrue();
        expect($states[2]['result']['adminState'])->toBeString();
    });
});

describe('Fiberhome Onts State Info - Error', function () {
    it('can get single state', function () {
        $this->fiberhome->interfaces(['NA-NA-0-0']);
        $this->fiberhome->serials(['CMSZ000000']);

        $states = $this->fiberhome->ontsStateInfo();

        expect($states)->toBeArray();
        expect($states[0]['success'])->toBeFalse();
        expect($states[0]['errorInfo'])->toBeString();
    });

    it('can get multiple powers', function () {
        $this->fiberhome->interfaces([$this->interface1, 'NA-NA-0-0', '']);
        $this->fiberhome->serials([$this->serial1, 'CMSZ000000', '']);

        $states = $this->fiberhome->ontsStateInfo();

        expect($states)->toBeArray();
        expect($states[0]['success'])->toBeTrue();
        expect($states[1]['success'])->toBeFalse();
        expect($states[1]['errorInfo'])->toBeString();
        expect($states[2]['success'])->toBeFalse();
        expect($states[2]['errorInfo'])->toBeString();
    });
});
