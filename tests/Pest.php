<?php

use PauloHortelan\OltMonitoring\Tests\TestCase;

function skipIfFakeConnection()
{
    if (config('connections.fake') === true) {
        test()->markTestSkipped('Connection with fake values');
    }
}

uses(TestCase::class)->in(__DIR__);
