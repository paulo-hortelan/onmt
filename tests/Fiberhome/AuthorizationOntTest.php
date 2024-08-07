<?php

use PauloHortelan\Onmt\Facades\Fiberhome;

uses()->group('Fiberhome');

beforeEach(function () {
    $ipOlt = env('FIBERHOME_OLT_IP');
    $ipServer = env('FIBERHOME_IP_SERVER');
    $username = env('FIBERHOME_OLT_USERNAME');
    $password = env('FIBERHOME_OLT_PASSWORD');

    $this->serial1 = env('FIBERHOME_SERIAL_1');
    $this->serial2 = env('FIBERHOME_SERIAL_2');
    $this->serial3 = env('FIBERHOME_SERIAL_3');

    $this->interface1 = env('FIBERHOME_INTERFACE_1');
    $this->interface2 = env('FIBERHOME_INTERFACE_2');
    $this->interface3 = env('FIBERHOME_INTERFACE_3');

    $this->ontType1 = env('FIBERHOME_ONT_TYPE_1');
    $this->ontType2 = env('FIBERHOME_ONT_TYPE_2');

    $this->pppoeUsername1 = env('FIBERHOME_PPPOE_USERNAME_1');
    $this->pppoeUsername2 = env('FIBERHOME_PPPOE_USERNAME_2');

    $this->portInterface1 = env('FIBERHOME_PORT_INTERFACE_1');
    $this->portInterface2 = env('FIBERHOME_PORT_INTERFACE_2');

    $this->vlan1 = env('FIBERHOME_VLAN_1');
    $this->vlan2 = env('FIBERHOME_VLAN_2');

    $this->ccos1 = env('FIBERHOME_CCOS_1');
    $this->ccos2 = env('FIBERHOME_CCOS_2');

    $this->fiberhome = Fiberhome::timeout(5, 10)->connect($ipOlt, $username, $password, $ipServer);
});

describe('Fiberhome Authorize Onts', function () {
    it('can authorize onts', function () {
        $authorizedOnts = $this->fiberhome->authorizeOnts([$this->interface1], [$this->serial1], [$this->ontType1], [$this->pppoeUsername1]);

        expect($authorizedOnts)->toBeArray();
        expect($authorizedOnts[0]['success'])->toBeTrue();
    });
});

describe('Fiberhome Configure Onts Vlan', function () {
    it('can configure onts vlan', function () {
        $configVlanOnts = $this->fiberhome->configureVlanOnts([$this->interface1], [$this->serial1], [$this->portInterface1], [$this->vlan1], [$this->ccos1]);

        expect($configVlanOnts)->toBeArray();
        expect($configVlanOnts[0]['success'])->toBeTrue();
    });
})->skip();

describe('Fiberhome Remove Onts', function () {
    it('can remove onts', function () {
        $removeOnts = $this->fiberhome->removeOnts([$this->interface1], [$this->serial1]);

        var_dump($removeOnts);

        expect($removeOnts)->toBeArray();
        expect($removeOnts[0]['success'])->toBeTrue();
    });
})->skip();
