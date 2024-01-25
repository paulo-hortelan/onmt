<?php

use PauloHortelan\Onmt\Facades\OltMonitor;
use PauloHortelan\Onmt\Models\Olt;
use PauloHortelan\Onmt\Services\OltMonitorService;

uses()->group('olt-monitor');

beforeEach(function () {
    Olt::create([
        'name' => 'olt-zte300',
        'host' => '120.0.1.7',
        'username' => 'test',
        'password' => 'pass1234',
        'brand' => 'ZTE',
        'model' => 'C300',
        'interface' => 'gpon-onu_1/',
    ]);

    Olt::create([
        'name' => 'olt-zte600',
        'host' => '120.0.1.8',
        'username' => 'test',
        'password' => 'pass1234',
        'brand' => 'ZTE',
        'model' => 'C600',
        'interface' => 'gpon_onu-1/',
    ]);
});

it('can connect on telnet', function () {
    $olt = Olt::firstWhere([['brand', 'ZTE'], ['model', 'C300']]);
    $Onmt = OltMonitor::connect($olt);

    expect($Onmt)->toBeInstanceOf(OltMonitorService::class);
})->skipIfFakeConnection();

it('can get ZTE300 ont optical power', function () {
    $olt = Olt::firstWhere([['brand', 'ZTE'], ['model', 'C300']]);
    $opticalPower = OltMonitor::connect($olt)->ontOpticalPower('gpon-onu_1/2/1:62');

    expect($opticalPower)->toBeFloat();
})->skipIfFakeConnection();

it('can get ZTE600 ont optical power', function () {
    $olt = Olt::firstWhere([['brand', 'ZTE'], ['model', 'C600']]);
    $opticalPower = OltMonitor::connect($olt)->ontOpticalPower('gpon_onu-1/1/1:5');

    expect($opticalPower)->toBeFloat();
})->skipIfFakeConnection();
