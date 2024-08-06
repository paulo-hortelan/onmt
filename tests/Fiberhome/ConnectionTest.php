<?php

use PauloHortelan\Onmt\Services\Connections\TL1;
use PauloHortelan\Onmt\Facades\Fiberhome;
use PauloHortelan\Onmt\Services\Fiberhome\FiberhomeService;

uses()->group('Fiberhome');

beforeEach(function () {
    $this->ipOlt = env('FIBERHOME_OLT_IP');
    $this->ipServer = env('FIBERHOME_IP_SERVER');
    $this->username = env('FIBERHOME_OLT_USERNAME');
    $this->password = env('FIBERHOME_OLT_PASSWORD');

    $this->serial1 = env('FIBERHOME_SERIAL_1');
});

describe('Fiberhome Connection TL1', function () {
    it('can create', function () {
        $telnet = new TL1($this->ipServer, 3337, 3, 3);

        $this->assertInstanceOf(TL1::class, $telnet);
    });

    it('can login', function () {
        $fiberhome = Fiberhome::connect($this->ipOlt, $this->username, $this->password, $this->ipServer);

        expect($fiberhome)->toBeInstanceOf(FiberhomeService::class);
    });
});
