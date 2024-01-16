<?php

use PauloHortelan\OltMonitoring\Facades\Zte300;
use PauloHortelan\OltMonitoring\Models\Olt;
use PauloHortelan\OltMonitoring\Services\Zte300Service;

uses()->group('zte300');

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
        'product_model' => 'C300',
    ]);
});

it('can connect on telnet', function () {
    $zte = Zte300::connect($this->olt);

    expect($zte)->toBeInstanceOf(Zte300Service::class);
})->skipIfFakeConnection();

it('can get ont optical power', function () {
    $opticalPower = Zte300::connect($this->olt)->ontOpticalPower($this->correctInterface);

    expect($opticalPower)->toBeFloat();
})->skipIfFakeConnection();

it('throws exception when cannot get ont optical power', function () {

    Zte300::connect($this->olt)->ontOpticalPower($this->wrongInterface);

})->throws(Exception::class)->skipIfFakeConnection();

it('can get ont interface', function () {
    $interface = Zte300::connect($this->olt)->ontInterface($this->correctSerial);

    expect($interface)->toStartWith('gpon-onu');
})->skipIfFakeConnection();

it('throws exception when cannot get ont interface', function () {

    Zte300::connect($this->olt)->ontInterface($this->wrongSerial);

})->throws(Exception::class)->skipIfFakeConnection();
