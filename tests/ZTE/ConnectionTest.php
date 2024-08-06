<?php

use PauloHortelan\Onmt\Services\Connections\Telnet;
use PauloHortelan\Onmt\Facades\ZTE;
use PauloHortelan\Onmt\Services\ZTE\ZTEService;

uses()->group('ZTE');

beforeEach(function () {
    $this->ipServer = env('ZTE_OLT_IP');
    $this->username = env('ZTE_OLT_USERNAME');
    $this->password = env('ZTE_OLT_PASSWORD');

    $this->serial1 = env('ZTE_SERIAL_1');
});

describe('ZTE Connection Telnet', function () {
    it('can create', function () {
        $telnet = new Telnet($this->ipServer, 23, 3, 3);

        $this->assertInstanceOf(Telnet::class, $telnet);
    });

    it('can login', function () {
        $zte = ZTE::connect($this->ipServer, $this->username, $this->password);

        expect($zte)->toBeInstanceOf(ZTEService::class);
    });
});
