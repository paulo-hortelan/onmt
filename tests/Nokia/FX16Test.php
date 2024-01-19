<?php

use PauloHortelan\OltMonitoring\Facades\Nokia;
use PauloHortelan\OltMonitoring\Models\Olt;
use PauloHortelan\OltMonitoring\Services\Nokia\NokiaService;

uses()->group('Nokia-FX16');

beforeEach(function () {
    $this->correctInterface = '1/1/1/1/8';
    $this->wrongInterface = '1/1/3/20/1';

    $this->correctSerial = 'ALCLFC5A84A7';
    $this->wrongSerial = 'ALCLB40D2CC1';

    $this->olt = Olt::create([
        'name' => 'olt-test1',
        'host' => '127.0.0.101',
        'username' => 'user',
        'password' => 'pass1234',
        'brand' => 'Nokia',
        'model' => 'FX16',
    ]);
});

// Create connection
it('can connect on telnet', function () {
    $zte = Nokia::connect($this->olt);

    expect($zte)->toBeInstanceOf(NokiaService::class);
})->skipIfFakeConnection();

// Optical power
it('can get ont optical power', function () {
    $opticalPower = Nokia::connect($this->olt)->ontOpticalPower($this->correctInterface);

    expect($opticalPower)->toBeFloat();
})->depends('it can connect on telnet');

it('throws exception when cannot get ont optical power', function () {
    Nokia::connect($this->olt)->ontOpticalPower($this->wrongInterface);
})->depends('it can connect on telnet')->throws(Exception::class);

// Interface
it('can get ont interface', function () {
    $interface = Nokia::connect($this->olt)->ontInterface($this->correctSerial);

    $this->assertNotNull($interface);
})->depends('it can connect on telnet');

it('throws exception when cannot get ont interface', function () {
    Nokia::connect($this->olt)->ontInterface($this->wrongSerial);
})->depends('it can connect on telnet')
    ->throws(Exception::class);

// Close connection
it('can close connection', function () {
    $zte = Nokia::connect($this->olt)->ontOpticalPower($this->correctInterface);
    $zte->disconnect();
    
    $zte->ontOpticalPower($this->correctInterface);
})->depends(
    'it can get ont optical power',
    'it throws exception when cannot get ont optical power',
    'it can get ont interface',
    'it throws exception when cannot get ont interface'
)->throws(Error::class);
