<?php

use PauloHortelan\Onmt\Facades\ZTE;
use PauloHortelan\Onmt\Services\Connections\Telnet;
use PauloHortelan\Onmt\Services\ZTE\ZTEService;

uses()->group('ZTE');

beforeEach(function () {
    $this->ipServerC300 = env('ZTE_C300_OLT_IP');
    $this->usernameTelnetC300 = env('ZTE_C300_OLT_USERNAME_TELNET');
    $this->passwordTelnetC300 = env('ZTE_C300_OLT_PASSWORD_TELNET');

    $this->ipServerC600 = env('ZTE_C600_OLT_IP');
    $this->usernameTelnetC600 = env('ZTE_C600_OLT_USERNAME_TELNET');
    $this->passwordTelnetC600 = env('ZTE_C600_OLT_PASSWORD_TELNET');
});

describe('ZTE C300 Connection Telnet', function () {
    it('can create', function () {
        $telnet = new Telnet($this->ipServerC300, 23, 3, 3);

        $this->assertInstanceOf(Telnet::class, $telnet);
    });

    it('can login', function () {
        $zte = ZTE::connectTelnet($this->ipServerC300, $this->usernameTelnetC300, $this->passwordTelnetC300, 23);

        expect($zte)->toBeInstanceOf(ZTEService::class);

        $zte->disconnect();
    });
});

describe('ZTE C600 Connection Telnet', function () {
    it('can create', function () {
        $telnet = new Telnet($this->ipServerC600, 23, 3, 3);

        $this->assertInstanceOf(Telnet::class, $telnet);
    });

    it('can login', function () {
        $zte = ZTE::connectTelnet($this->ipServerC600, $this->usernameTelnetC600, $this->passwordTelnetC600, 23, null, 'C600');

        expect($zte)->toBeInstanceOf(ZTEService::class);

        $zte->disconnect();
    });
});
