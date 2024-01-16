<?php

use PauloHortelan\OltMonitoring\Connections\Telnet;
use PauloHortelan\OltMonitoring\Connections\TL1;

uses()->group('connections');

it('can connect Telnet', function () {
    $telnet = new Telnet('127.0.0.2', 23, 3, 3);
    $telnet->stripPromptFromBuffer(true);

    $this->assertInstanceOf(Telnet::class, $telnet);
})->skipIfFakeConnection();

it('can connect TL1', function () {
    $tl1 = new TL1('127.0.0.4', 3337, 3, 3);
    $tl1->stripPromptFromBuffer(true);

    $this->assertInstanceOf(TL1::class, $tl1);
})->skipIfFakeConnection();
