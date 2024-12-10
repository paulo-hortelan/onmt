<?php

use PauloHortelan\Onmt\Facades\Nokia;
use PauloHortelan\Onmt\Services\Connections\Telnet;
use PauloHortelan\Onmt\Services\Connections\TL1;
use PauloHortelan\Onmt\Services\Nokia\NokiaService;

uses()->group('Nokia');

beforeEach(function () {
    $this->ipServer = env('NOKIA_OLT_IP');
    $this->usernameTelnet = env('NOKIA_OLT_USERNAME_TELNET');
    $this->passwordTelnet = env('NOKIA_OLT_PASSWORD_TELNET');
    $this->usernameTL1 = env('NOKIA_OLT_USERNAME_TL1');
    $this->passwordTL1 = env('NOKIA_OLT_PASSWORD_TL1');
});

describe('Nokia Connection Telnet', function () {
    it('can create', function () {
        $telnet = new Telnet($this->ipServer, 23, 3, 3);

        $this->assertInstanceOf(Telnet::class, $telnet);
    });

    it('can login', function () {
        $nokia = Nokia::connectTelnet($this->ipServer, $this->usernameTelnet, $this->passwordTelnet, 23);

        expect($nokia)->toBeInstanceOf(NokiaService::class);

        $nokia->disconnect();
    });
});

describe('Nokia Connection TL1', function () {
    it('can create', function () {
        $telnet = new TL1($this->ipServer, 1022, 3, 3);

        $this->assertInstanceOf(Telnet::class, $telnet);
    });

    it('can login', function () {
        $nokia = Nokia::connectTL1($this->ipServer, $this->usernameTL1, $this->passwordTL1, 1023);

        expect($nokia)->toBeInstanceOf(NokiaService::class);

        $nokia->disconnect();
    });
});
