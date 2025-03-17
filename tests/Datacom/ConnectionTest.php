<?php

use PauloHortelan\Onmt\Facades\Datacom;
use PauloHortelan\Onmt\Services\Connections\Telnet;
use PauloHortelan\Onmt\Services\Datacom\DatacomService;

uses()->group('Datacom');

beforeEach(function () {
    $this->ipServer = env('DATACOM_DM4612_OLT_IP');
    $this->usernameTelnet = env('DATACOM_DM4612_OLT_USERNAME_TELNET');
    $this->passwordTelnet = env('DATACOM_DM4612_OLT_PASSWORD_TELNET');
});

describe('Datacom Connection Telnet', function () {
    it('can create', function () {
        $telnet = new Telnet($this->ipServer, 23, 3, 3);

        $this->assertInstanceOf(Telnet::class, $telnet);
    });

    it('can login', function () {
        $datacom = Datacom::connectTelnet($this->ipServer, $this->usernameTelnet, $this->passwordTelnet, 23);

        expect($datacom)->toBeInstanceOf(DatacomService::class);

        $datacom->disconnect();
    });
});
