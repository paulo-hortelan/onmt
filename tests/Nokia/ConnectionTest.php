<?php

use PauloHortelan\Onmt\Facades\Nokia;
use PauloHortelan\Onmt\Services\Connections\Telnet;
use PauloHortelan\Onmt\Services\Connections\TL1;
use PauloHortelan\Onmt\Services\Nokia\NokiaService;

uses()->group('Nokia');

beforeEach(function () {
    $this->ipServer = env('NOKIA_OLT_IP');
    $this->username = env('NOKIA_OLT_USERNAME');
    $this->password = env('NOKIA_OLT_PASSWORD');

    $this->serial1 = env('NOKIA_SERIAL_1');
});

describe('Nokia Connection Telnet', function () {
    it('can create', function () {
        $telnet = new Telnet($this->ipServer, 23, 3, 3);

        $this->assertInstanceOf(Telnet::class, $telnet);
    });

    it('can login', function () {
        $nokia = Nokia::connectTelnet($this->ipServer, $this->username, $this->password, 23);

        expect($nokia)->toBeInstanceOf(NokiaService::class);
    });
});

describe('Nokia Connection TL1', function () {
    it('can create', function () {
        $telnet = new TL1($this->ipServer, 1022, 3, 3);

        $this->assertInstanceOf(Telnet::class, $telnet);
    });

    it('can login', function () {
        $nokia = Nokia::connectTL1($this->ipServer, $this->username, $this->password, 1022);

        expect($nokia)->toBeInstanceOf(NokiaService::class);
    });
});
