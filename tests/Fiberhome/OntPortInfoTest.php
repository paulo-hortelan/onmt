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

describe('Fiberhome Onts Port Info - Success', function () {
    it('can get single info', function () {
        $ports = $this->fiberhome->ontsPortInfo([$this->interface1], [$this->serial1]);

        expect($ports)->toBeArray();
        expect($ports[0]['success'])->toBeTrue();
        expect($ports[0]['result']['cvLan'])->toBeInt();

        $ports = $this->fiberhome->interface($this->interface1)->serial($this->serial1)->ontsPortInfo();

        expect($ports)->toBeArray();
        expect($ports[0]['success'])->toBeTrue();
        expect($ports[0]['result']['cvLan'])->toBeInt();
    });

    it('can get multiple infos', function () {
        $interfaces = [$this->interface1, $this->interface2, $this->interface3];
        $serials = [$this->serial1, $this->serial2, $this->serial3];

        $ports = $this->fiberhome->ontsPortInfo($interfaces, $serials);

        expect($ports)->toBeArray();
        expect($ports[0]['success'])->toBeTrue();
        expect($ports[0]['result']['cvLan'])->toBeInt();
        expect($ports[1]['success'])->toBeTrue();
        expect($ports[1]['result']['cvLan'])->toBeInt();
        expect($ports[2]['success'])->toBeTrue();
        expect($ports[2]['result']['cvLan'])->toBeInt();

        $ports = $this->fiberhome->interfaces($interfaces)->serials($serials)->ontsPortInfo();

        expect($ports)->toBeArray();
        expect($ports[0]['success'])->toBeTrue();
        expect($ports[0]['result']['cvLan'])->toBeInt();
        expect($ports[1]['success'])->toBeTrue();
        expect($ports[1]['result']['cvLan'])->toBeInt();
        expect($ports[2]['success'])->toBeTrue();
        expect($ports[2]['result']['cvLan'])->toBeInt();
    });
});