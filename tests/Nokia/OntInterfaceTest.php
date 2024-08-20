<?php

use PauloHortelan\Onmt\Facades\Nokia;

uses()->group('Nokia');

beforeEach(function () {
    $ipServer = env('NOKIA_OLT_IP');
    $username = env('NOKIA_OLT_USERNAME');
    $password = env('NOKIA_OLT_PASSWORD');

    $this->serial1 = env('NOKIA_SERIAL_1');
    $this->serial2 = env('NOKIA_SERIAL_2');
    $this->serial3 = env('NOKIA_SERIAL_3');

    $this->nokia = Nokia::connect($ipServer, $username, $password);
});

describe('Nokia Optical Interface', function () {
    it('can get single interface', function () {
        $interface = $this->nokia->ontsInterface([$this->serial1])[0];

        expect($interface['result']['interface'])->toBeString();

        $interface = $this->nokia->serial($this->serial1)->ontsInterface()[0];

        expect($interface['result']['interface'])->toBeString();
    });

    it('can get multiple interfaces', function () {
        $serials = [$this->serial1, $this->serial2, $this->serial3];

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
