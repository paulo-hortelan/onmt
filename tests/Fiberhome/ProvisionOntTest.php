<?php

use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\LanConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\VeipConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\WanConfig;
use PauloHortelan\Onmt\Facades\Fiberhome;

uses()->group('Fiberhome');

beforeEach(function () {
    $this->ipOlt = env('FIBERHOME_OLT_IP');
    $this->ipServer = env('FIBERHOME_IP_SERVER');
    $this->username = env('FIBERHOME_OLT_USERNAME_TL1');
    $this->password = env('FIBERHOME_OLT_PASSWORD_TL1');

    $this->serialALCL = env('FIBERHOME_SERIAL_ALCL');
    $this->serialCMSZ = env('FIBERHOME_SERIAL_CMSZ');
    $this->serialFHTT = env('FIBERHOME_SERIAL_FHTT');

    $this->ponInterface = env('FIBERHOME_PON_INTERFACE');

    $this->ontTypeALCL = env('FIBERHOME_ONT_TYPE_ALCL');
    $this->ontTypeCMSZ = env('FIBERHOME_ONT_TYPE_CMSZ');
    $this->ontTypeFHTT = env('FIBERHOME_ONT_TYPE_FHTT');

    $this->pppoeUsername = env('FIBERHOME_PPPOE_USERNAME');

    $this->portInterfaceALCL = env('FIBERHOME_PORT_INTERFACE_ALCL');
    $this->portInterfaceCMSZ = env('FIBERHOME_PORT_INTERFACE_CMSZ');
    $this->portInterfaceFHTT = env('FIBERHOME_PORT_INTERFACE_FHTT');

});

describe('Fiberhome Provision Onts Router-Nokia', function () {
    it('can provision onts', function () {
        $this->fiberhome = Fiberhome::timeout(5, 10)->connectTL1($this->ipOlt, $this->username, $this->password, 3337, $this->ipServer);

        $this->fiberhome->startRecordingCommands(
            description: 'Provision Router-Nokia',
            ponInterface: $this->ponInterface,
            interface: null,
            serial: $this->serialALCL
        );

        $this->fiberhome->serials([$this->serialALCL]);

        $provisionedOnts = $this->fiberhome->authorizeOnts($this->ponInterface, $this->ontTypeALCL, $this->pppoeUsername);

        expect($provisionedOnts->first()->allCommandsSuccessful())->toBeTrue();

        $veipConfig = new VeipConfig(
            serviceId: 1,
            cVlanId: 110,
            serviceModelProfile: 'AonetVEIP',
            serviceType: 'DATA',
        );

        $configuredOnts = $this->fiberhome->configureVeipOnts($this->ponInterface, $this->portInterfaceALCL, $veipConfig);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $commandBatchResult = $this->nokiaTL1->stopRecordingCommands();

        expect($commandBatchResult->allCommandsSuccessful())->toBeTrue();
    });
})->skip();

describe('Fiberhome Provision Onts Router-Fiberhome', function () {
    it('can provision onts', function () {
        $this->fiberhome = Fiberhome::timeout(5, 10)->connectTL1($this->ipOlt, $this->username, $this->password, 3337, $this->ipServer);

        $this->fiberhome->startRecordingCommands(
            description: 'Provision Router-Fiberhome',
            ponInterface: null,
            interface: $this->interfaceFHTT,
            serial: $this->serialFHTT
        );

        $this->fiberhome->serials([$this->serialFHTT]);

        $provisionedOnts = $this->fiberhome->authorizeOnts($this->ponInterface, $this->ontTypeFHTT, $this->pppoeUsername);

        expect($provisionedOnts->first()->allCommandsSuccessful())->toBeTrue();

        $wanConfig = new WanConfig(
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

        // UPORT = 0
        $wanConfig->uPort = 0;
        $wanConfig->ssdId = null;

        $configuredOnts = $this->fiberhome->configureWanOnts($this->ponInterface, $wanConfig);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        // SSDID = 1
        $wanConfig->uPort = null;
        $wanConfig->ssdId = 1;

        $configuredOnts = $this->fiberhome->configureWanOnts($this->ponInterface, $wanConfig);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        // SSDID = 5
        $wanConfig->uPort = null;
        $wanConfig->ssdId = 5;

        $configuredOnts = $this->fiberhome->configureWanOnts($this->ponInterface, $wanConfig);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $commandBatchResult = $this->nokiaTL1->stopRecordingCommands();

        expect($commandBatchResult->allCommandsSuccessful())->toBeTrue();
    });
})->skip();

describe('Fiberhome Provision Onts Bridge-Fiberhome', function () {
    it('can provision onts', function () {
        $this->fiberhome = Fiberhome::timeout(5, 10)->connectTL1($this->ipOlt, $this->username, $this->password, 3337, $this->ipServer);

        $this->fiberhome->startRecordingCommands(
            description: 'Provision Bridge-Fiberhome',
            ponInterface: null,
            interface: $this->interfaceCMSZ,
            serial: $this->serialCMSZ
        );

        $this->fiberhome->serials([$this->serialCMSZ]);

        $provisionedOnts = $this->fiberhome->authorizeOnts($this->ponInterface, $this->ontTypeFHTT, $this->pppoeUsername);

        expect($provisionedOnts->first()->allCommandsSuccessful())->toBeTrue();

        $lanConfig = new LanConfig(
            cVlan: 110,
            cCos: 0,
        );

        $configuredOnts = $this->fiberhome->configureLanOnts($this->ponInterface, $this->portInterfaceCMSZ, $lanConfig);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $commandBatchResult = $this->fiberhome->stopRecordingCommands();

        expect($commandBatchResult->allCommandsSuccessful())->toBeTrue();
    });
})->skip();
