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

    $this->zte = ZTE::connect($ipServer, $username, $password);
});

describe('ZTE Optical Interface', function () {
    it('can get single interface', function () {
        $interface = $this->zte->opticalInterfaces([$this->serial1])[0];

        expect($interface['result']['interface'])->toBeString();

        $interface = $this->zte->serial($this->serial1)->opticalInterfaces()[0];

        expect($interface['result']['interface'])->toBeString();
    });

    it('can get multiple interfaces', function () {
        $serials = [$this->serial1, $this->serial2, $this->serial3];

        $interfaces = $this->zte->opticalInterfaces($serials);

        expect($interfaces[0]['result']['interface'])->toBeString();
        expect($interfaces[1]['result']['interface'])->toBeString();
        expect($interfaces[2]['result']['interface'])->toBeString();

        $interfaces = $this->zte->serials($serials)->opticalInterfaces();

        expect($interfaces[0]['result']['interface'])->toBeString();
        expect($interfaces[1]['result']['interface'])->toBeString();
        expect($interfaces[2]['result']['interface'])->toBeString();
    });
});

afterAll(function () {
    $this->zte->disconnect();
});
