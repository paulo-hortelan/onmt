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

    $this->nokia = Nokia::connectTelnet($ipOlt, $username, $password, 23);
});

describe('Nokia Unregistered Onts - Success', function () {
    it('can get unregistered onts', function () {
        $unregisteredOnts = $this->nokia->unregisteredOnts();

        var_dump($unregisteredOnts);

        expect($unregisteredOnts)->toBeArray();
        expect($unregisteredOnts[0]['success'])->toBeTrue();
    });
});
