<?php

use PauloHortelan\Onmt\Facades\Nokia;

uses()->group('Nokia');

beforeEach(function () {
    $ipOlt = env('NOKIA_OLT_IP');
    $username = env('NOKIA_OLT_USERNAME');
    $password = env('NOKIA_OLT_PASSWORD');

    $this->serial1 = env('NOKIA_SERIAL_1');
    $this->serial2 = env('NOKIA_SERIAL_2');
    $this->serial3 = env('NOKIA_SERIAL_3');

    $this->interface1 = env('NOKIA_INTERFACE_1');
    $this->interface2 = env('NOKIA_INTERFACE_2');
    $this->interface3 = env('NOKIA_INTERFACE_3');

    $this->pppoeUsername1 = env('NOKIA_PPPOE_USERNAME_1');

    $this->swVerPlnd1 = env('NOKIA_SWVERPLND_1');

    $this->ponInterface1 = env('NOKIA_PON_INTERFACE_1');

    $this->nokiaTelnet = Nokia::connectTelnet($ipOlt, $username, $password, 23);
    $this->nokiaTL1 = Nokia::connectTL1($ipOlt, $username, $password, 1022);
});

describe('Nokia Provision Onts - Success', function () {
    it('can provision single bridge ont', function () {

        // $ontIndex = $this->nokia->getNextOntsIndex([$this->ponInterface1]);

        // $interface = $this->ponInterface1[0] . '/' . $ontIndex;
        // $this->nokia->interfaces([$this->interface1]);

        $ontIndex = $this->nokiaTelnet->getNextOntIndex($this->ponInterface1);

        $ontNblkCommand =
            [
                "DESC1" => $this->pppoeUsername1,
                "DESC2" => $this->pppoeUsername1,
                "SWVERPLND" => $this->swVerPlnd1,
            ];

        $this->nokiaTL1->interfaces([$this->ponInterface1 . '/' . $ontIndex])->serials([$this->serial1]);

        $provisionedOnts = $this->nokiaTL1->provisionOnt([], [], $ontNblkCommand);

        var_dump($provisionedOnts);


        // var_dump($interface);

        // $authorizedOnts = $this->nokia->provisionBridgeOnts([$this->pppoeUsername1], [$this->swVerPlnd1]);

        // var_dump($authorizedOnts);

        // expect($authorizedOnts)->toBeArray();
        // expect($authorizedOnts[0]['success'])->toBeTrue();
    });
});

describe('Nokia Remove Onts', function () {
    it('can remove onts', function () {
        $removedOnts = $this->nokiaTelnet->interfaces([$this->interface1])->removeOnts();

        var_dump($removedOnts);

        expect($removedOnts)->toBeArray();
        expect($removedOnts[0]['success'])->toBeTrue();
    })->skip();
});
