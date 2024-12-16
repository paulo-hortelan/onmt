<?php

use PauloHortelan\Onmt\Facades\Fiberhome;
use PauloHortelan\Onmt\Services\Connections\TL1;
use PauloHortelan\Onmt\Services\Fiberhome\FiberhomeService;

uses()->group('Fiberhome');

beforeEach(function () {
    $this->ipOlt = env('FIBERHOME_OLT_IP');
    $this->ipServer = env('FIBERHOME_IP_SERVER');
    $this->username = env('FIBERHOME_OLT_USERNAME_TL1');
    $this->password = env('FIBERHOME_OLT_PASSWORD_TL1');
});

describe('Fiberhome Connection TL1', function () {
    it('can create', function () {
        $telnet = new TL1($this->ipServer, 3337, 3, 3);

        $this->assertInstanceOf(TL1::class, $telnet);

        $telnet->disconnect();
    });

    it('can login', function () {
        $fiberhome = Fiberhome::connectTL1($this->ipOlt, $this->username, $this->password, 3337, $this->ipServer);

        $fiberhome->enableDebug();

        expect($fiberhome)->toBeInstanceOf(FiberhomeService::class);

        $fiberhome->disconnect();
    })->only();
});
