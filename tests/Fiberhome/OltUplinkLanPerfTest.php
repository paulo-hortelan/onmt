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

describe('Fiberhome Optical Lan Perf - Success', function () {
    it('can get single lan info', function () {
        $lanPerfs = $this->fiberhome->opticalLanPerfs([$this->interface1], [$this->serial1]);

        // expect($lans)->toBeArray();
        // expect($lans[0]['success'])->toBeTrue();
        // expect($lans[0]['result']['adminStatus'])->toBeString();

        // $lans = $this->fiberhome->interface($this->interface1)->serial($this->serial1)->opticalLanPerfs();

        // expect($lans)->toBeArray();
        // expect($lans[0]['success'])->toBeTrue();
        // expect($lans[0]['result']['adminStatus'])->toBeString();
    });

    it('can get multiple lan infos', function () {
        $interfaces = [$this->interface1, $this->interface2, $this->interface3];
        $serials = [$this->serial1, $this->serial2, $this->serial3];

        $lans = $this->fiberhome->opticalLanInfos($interfaces, $serials);

        expect($lans)->toBeArray();
        expect($lans[0]['success'])->toBeTrue();
        expect($lans[0]['result']['adminStatus'])->toBeString();
        expect($lans[1]['success'])->toBeTrue();
        expect($lans[1]['result']['adminStatus'])->toBeString();
        expect($lans[2]['success'])->toBeTrue();
        expect($lans[2]['result']['adminStatus'])->toBeString();

        $lans = $this->fiberhome->interfaces($interfaces)->serials($serials)->opticalLanInfos();

        expect($lans)->toBeArray();
        expect($lans[0]['success'])->toBeTrue();
        expect($lans[0]['result']['adminStatus'])->toBeString();
        expect($lans[1]['success'])->toBeTrue();
        expect($lans[1]['result']['adminStatus'])->toBeString();
        expect($lans[2]['success'])->toBeTrue();
        expect($lans[2]['result']['adminStatus'])->toBeString();
    });
});

afterAll(function () {
    $this->fiberhome->disconnect();
});
