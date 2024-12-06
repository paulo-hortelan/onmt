<?php

use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\LanServiceConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\VeipServiceConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\WanServiceConfig;
use PauloHortelan\Onmt\Facades\Fiberhome;

uses()->group('Fiberhome');

beforeEach(function () {
    $ipOlt = env('FIBERHOME_OLT_IP');
    $ipServer = env('FIBERHOME_IP_SERVER');
    $username = env('FIBERHOME_OLT_USERNAME');
    $password = env('FIBERHOME_OLT_PASSWORD');

    $this->serialALCL = env('FIBERHOME_SERIAL_ALCL');
    $this->serialCMSZ = env('FIBERHOME_SERIAL_CMSZ');
    $this->serialFHTT = env('FIBERHOME_SERIAL_FHTT');

    $this->interfaceALCL = env('FIBERHOME_INTERFACE_ALCL');
    $this->interfaceCMSZ = env('FIBERHOME_INTERFACE_CMSZ');
    $this->interfaceFHTT = env('FIBERHOME_INTERFACE_FHTT');

    $this->ontTypeALCL = env('FIBERHOME_ONT_TYPE_ALCL');
    $this->ontTypeCMSZ = env('FIBERHOME_ONT_TYPE_CMSZ');
    $this->ontTypeFHTT = env('FIBERHOME_ONT_TYPE_FHTT');

    $this->pppoeUsername = env('FIBERHOME_PPPOE_USERNAME');

    $this->portInterfaceALCL = env('FIBERHOME_PORT_INTERFACE_ALCL');
    $this->portInterfaceCMSZ = env('FIBERHOME_PORT_INTERFACE_CMSZ');
    $this->portInterfaceFHTT = env('FIBERHOME_PORT_INTERFACE_FHTT');

    $this->fiberhome = Fiberhome::timeout(5, 10)->connect($ipOlt, $username, $password, 3337, $ipServer);

});

describe('Fiberhome Provision Onts Router-Nokia', function () {
    it('can provision onts', function () {
        $this->fiberhome->interfaces([$this->interfaceALCL])->serials([$this->serialALCL]);

        $veipConfig = new VeipServiceConfig(
            serviceId: 1,
            cVlanId: 110,
            serviceModelProfile: 'AonetVEIP',
            serviceType: 'DATA',
        );

        $provisionedOnts = $this->fiberhome->provisionRouterVeipOnts($this->ontTypeALCL, $this->pppoeUsername, $this->portInterfaceALCL, $veipConfig);

        expect($provisionedOnts)->toBeArray();
        expect($provisionedOnts[0]['success'])->toBeTrue();
    });
})->skip();

describe('Fiberhome Provision Onts Router-Fiberhome', function () {
    it('can provision onts', function () {
        $this->fiberhome->interfaces([$this->interfaceFHTT])->serials([$this->serialFHTT]);

        $wanServiceConfig = new WanServiceConfig(
            status: 1,
            mode: 2,
            connType: 2,
            vlan: 110,
            cos: 7,
            qos: 1,
            nat: 1,
            ipMode: 3,
            pppoeProxy: 2,
            pppoeUser: $this->pppoeUsername,
            pppoePasswd: '1234',
            pppoeName: '',
            pppoeMode: 1,
            uPort: 0,
            ssdId: null
        );

        $provisionedOnts = $this->fiberhome->provisionRouterWanOnts($this->ontTypeFHTT, $this->pppoeUsername, $wanServiceConfig);

        expect($provisionedOnts)->toBeArray();
        expect($provisionedOnts[0]['success'])->toBeTrue();
    });
})->skip();

describe('Fiberhome Provision Onts Bridge-Fiberhome', function () {
    it('can provision onts', function () {
        $this->fiberhome->interfaces([$this->interfaceCMSZ])->serials([$this->serialCMSZ]);

        $lanServiceConfig = new LanServiceConfig(
            cVlan: 110,
            cCos: 0,
        );

        $provisionedOnts = $this->fiberhome->provisionBridgeOnts($this->ontTypeCMSZ, $this->pppoeUsername, $this->portInterfaceCMSZ, $lanServiceConfig);

        expect($provisionedOnts)->toBeArray();
        expect($provisionedOnts[0]['success'])->toBeTrue();
    });
})->skip();
