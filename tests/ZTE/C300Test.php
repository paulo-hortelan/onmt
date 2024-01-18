<?php

use PauloHortelan\OltMonitoring\Facades\ZTE;
use PauloHortelan\OltMonitoring\Models\Olt;
use PauloHortelan\OltMonitoring\Services\ZTE\ZTEService;

uses()->group('ZTE-C300');

beforeEach(function () {
    $this->correctInterface = 'gpon-onu_1/2/1:62';
    $this->wrongInterface = 'gpon-onu_1/2/1:99';

    $this->correctSerial = 'ALCLB40D2BB0';
    $this->wrongSerial = 'ALCLB40D2CC1';

    $this->olt = Olt::create([
        'name' => 'olt-test1',
        'host' => '127.0.0.3',
        'username' => 'user',
        'password' => 'pass1234',
        'brand' => 'ZTE',
        'model' => 'C300',
    ]);
});

it('can connect on telnet', function () {
    $zte = ZTE::connect($this->olt);

    expect($zte)->toBeInstanceOf(ZTEService::class);
})->skipIfFakeConnection();

it('can get ont optical power', function () {
    $opticalPower = ZTE::connect($this->olt)->ontOpticalPower($this->correctInterface);

    expect($opticalPower)->toBeFloat();
})->skipIfFakeConnection();

it('throws exception when cannot get ont optical power', function () {

    ZTE::connect($this->olt)->ontOpticalPower($this->wrongInterface);

})->throws(Exception::class)->skipIfFakeConnection();

it('can get ont interface', function () {
    $interface = ZTE::connect($this->olt)->ontInterface($this->correctSerial);

    expect($interface)->toStartWith('gpon-onu');
})->skipIfFakeConnection();

it('throws exception when cannot get ont interface', function () {

    ZTE::connect($this->olt)->ontInterface($this->wrongSerial);

})->throws(Exception::class)->skipIfFakeConnection();
