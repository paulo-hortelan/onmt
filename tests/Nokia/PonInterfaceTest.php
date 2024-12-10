<?php

use PauloHortelan\Onmt\Facades\Nokia;

uses()->group('Nokia');

beforeEach(function () {
    $ipOlt = env('NOKIA_OLT_IP');
    $username = env('NOKIA_OLT_USERNAME_TELNET');
    $password = env('NOKIA_OLT_PASSWORD_TELNET');

    $this->serial1 = env('NOKIA_SERIAL_1');
    $this->serial2 = env('NOKIA_SERIAL_2');
    $this->serial3 = env('NOKIA_SERIAL_3');

    $this->interface1 = env('NOKIA_INTERFACE_1');
    $this->interface2 = env('NOKIA_INTERFACE_2');
    $this->interface3 = env('NOKIA_INTERFACE_3');

    $this->ponInterface1 = env('NOKIA_PON_INTERFACE_1');

    $this->nokia = Nokia::connectTelnet($ipOlt, $username, $password, 23);
});

describe('Nokia Onts by Pon Interface - Success', function () {
    it('can get onts', function () {
        $onts = $this->nokia->ontsByPonInterfaces([$this->ponInterface1]);

        expect($onts)->toBeArray();
        expect($onts[0]['success'])->toBeTrue();
    });

    it('can get next ont index', function () {
        $nextOntIndex = $this->nokia->getNextOntIndex($this->ponInterface1);

        var_dump($nextOntIndex);

        expect($nextOntIndex)->toBeInt();
    })->only();
});
