<?php

use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\LanConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\VeipConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\WanConfig;
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

    $this->fiberhome = Fiberhome::timeout(5, 10)->connect($ipOlt, $username, $password, 3337, $ipServer);

});

describe('Fiberhome Authorize Onts', function () {
    it('can authorize onts', function () {
        $this->fiberhome->interfaces([$this->interfaceALCL, $this->interfaceCMSZ])->serials([$this->serialALCL, $this->serialCMSZ]);

        $configuredOnts = $this->fiberhome->authorizeOnts($this->ontTypeALCL, $this->pppoeUsername);

        expect($configuredOnts)->toBeArray();
        expect($configuredOnts[0]['success'])->toBeTrue();
    });
})->skip();

describe('Fiberhome Configure Onts LAN', function () {
    it('can configure onts lan', function () {
        $this->fiberhome->interfaces([$this->interfaceCMSZ])->serials([$this->serialCMSZ]);

        $lanConfig = new LanConfig(
            cVlan: 110,
            cCos: 0,
        );

        $configuredOnts = $this->fiberhome->configureLanOnts($this->portInterfaceCMSZ, $lanConfig);

        expect($configuredOnts)->toBeArray();
        expect($configuredOnts[0]['success'])->toBeTrue();
    });
})->skip();

describe('Fiberhome Configure Onts VEIP', function () {
    it('can configure onts veip', function () {
        $this->fiberhome->interfaces([$this->interfaceALCL])->serials([$this->serialALCL]);

        $veipConfig = new VeipConfig(
            serviceId: 1,
            cVlanId: 110,
            serviceModelProfile: 'AonetVEIP',
            serviceType: 'DATA',
        );

        $configuredOnts = $this->fiberhome->configureVeipOnts($this->portInterfaceALCL, $veipConfig);

        expect($configuredOnts)->toBeArray();
        expect($configuredOnts[0]['success'])->toBeTrue();
    });
})->skip();

describe('Fiberhome Configure Onts WAN', function () {
    it('can configure onts wan', function () {
        $this->fiberhome->interfaces([$this->interfaceFHTT])->serials([$this->serialFHTT]);

        $WanConfig = new WanConfig(
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

        $configuredOnts = $this->fiberhome->configureWanOnts($WanConfig);

        expect($configuredOnts)->toBeArray();
        expect($configuredOnts[0]['success'])->toBeTrue();
    });
})->skip();

describe('Fiberhome Remove Onts', function () {
    it('can remove onts', function () {
        $this->fiberhome->interfaces([$this->interfaceALCL, $this->interfaceCMSZ, $this->interfaceFHTT])
            ->serials([$this->serialALCL, $this->serialCMSZ, $this->serialFHTT]);

        $removedOnts = $this->fiberhome->removeOnts();

        expect($removedOnts)->toBeArray();
        expect($removedOnts[0]['success'])->toBeTrue();
    });
})->only();
