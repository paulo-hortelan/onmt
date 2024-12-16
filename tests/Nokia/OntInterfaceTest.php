<?php

use PauloHortelan\Onmt\Facades\Nokia;

uses()->group('Nokia');

beforeEach(function () {
    $ipServer = env('NOKIA_OLT_IP');
    $username = env('NOKIA_OLT_USERNAME_TELNET');
    $password = env('NOKIA_OLT_PASSWORD_TELNET');

    $this->serialALCL = env('NOKIA_SERIAL_ALCL');
    $this->serialCMSZ = env('NOKIA_SERIAL_CMSZ');

    $this->nokia = Nokia::connectTelnet($ipServer, $username, $password, 23);
});

describe('Nokia Optical Interface', function () {
    it('can get single interface', function () {
        $interface = $this->nokia->ontsInterface([$this->serialALCL])[0];

        expect($interface['result']['interface'])->toBeString();

        $interface = $this->nokia->serial($this->serialALCL)->ontsInterface()[0];

        expect($interface['result']['interface'])->toBeString();
    });

    it('can get multiple interfaces', function () {
        $serials = [$this->serialALCL, $this->serialCMSZ];

        $interfaces = $this->nokia->ontsInterface($serials);

        expect($interfaces[0]['result']['interface'])->toBeString();
        expect($interfaces[1]['result']['interface'])->toBeString();
        expect($interfaces[2]['result']['interface'])->toBeString();

        $interfaces = $this->nokia->serials($serials)->ontsInterface();

        expect($interfaces[0]['result']['interface'])->toBeString();
        expect($interfaces[1]['result']['interface'])->toBeString();
        expect($interfaces[2]['result']['interface'])->toBeString();
    });
});

afterAll(function () {
    $this->nokia->disconnect();
});
