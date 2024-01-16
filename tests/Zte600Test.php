<?php

use PauloHortelan\OltMonitoring\Facades\Zte600;
use PauloHortelan\OltMonitoring\Models\Olt;
use PauloHortelan\OltMonitoring\Services\Zte600Service;

uses()->group('zte600');

beforeEach(function () {
    $this->correctInterface = 'gpon-onu_1/2/1:62';
    $this->wrongInterface = 'gpon-onu_1/2/1:99';

    $this->correctSerial = 'ALCLB30G2BB0';
    $this->wrongSerial = 'ALCLB40D7AC1';

    $this->olt = Olt::create([
        'name' => 'olt-test1',
        'host' => '127.0.0.4',
        'username' => 'user',
        'password' => 'pass1234',
        'brand' => 'ZTE',
        'product_model' => 'C600',
    ]);
});

it('can connect on telnet', function () {
    $zte = Zte600::connect($this->olt);

    expect($zte)->toBeInstanceOf(Zte600Service::class);
})->skipIfFakeConnection();

it('can get ont optical power', function () {
    $opticalPower = Zte600::connect($this->olt)->ontOpticalPower($this->correctInterface);

    expect($opticalPower)->toBeFloat();
})->skipIfFakeConnection();

it('throws exception when cannot get ont optical power', function () {

    Zte600::connect($this->olt)->ontOpticalPower($this->wrongInterface);

})->throws(Exception::class)->skipIfFakeConnection();

it('can get ont interface', function () {
    $interface = Zte600::connect($this->olt)->ontInterface($this->correctSerial);

    expect($interface)->toStartWith('gpon-onu');
})->skipIfFakeConnection();

it('throws exception when cannot get ont interface', function () {

    Zte600::connect($this->olt)->ontInterface($this->wrongSerial);

})->throws(Exception::class)->skipIfFakeConnection();
