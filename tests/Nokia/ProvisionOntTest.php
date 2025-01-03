<?php

use PauloHortelan\Onmt\DTOs\Nokia\FX16\EdOntConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EdOntVeipConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntLogPortConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntOntCardConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntOntConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\QosUsQueueConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\VlanEgPortConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\VlanPortConfig;
use PauloHortelan\Onmt\Facades\Nokia;

uses()->group('Nokia');

beforeEach(function () {
    $this->ipOlt = env('NOKIA_OLT_IP');

    $this->usernameTelnet = env('NOKIA_OLT_USERNAME_TELNET');
    $this->passwordTelnet = env('NOKIA_OLT_PASSWORD_TELNET');
    $this->usernameTL1 = env('NOKIA_OLT_USERNAME_TL1');
    $this->passwordTL1 = env('NOKIA_OLT_PASSWORD_TL1');

    $this->serialALCL = env('NOKIA_SERIAL_ALCL');
    $this->serialCMSZ = env('NOKIA_SERIAL_CMSZ');

    $this->interfaceALCL = env('NOKIA_INTERFACE_ALCL');

    $this->pppoeUsername = env('NOKIA_PPPOE_USERNAME');
    $this->pppoePassword = env('NOKIA_PPPOE_PASSWORD');

    $this->ponInterface = env('NOKIA_PON_INTERFACE');
});

describe('Nokia Complete Provision and Configuration on ONTs - Router Nokia', function () {
    it('can realize a complete provision and configuration', function () {
        $nokiaTelnet = Nokia::connectTelnet($this->ipOlt, $this->usernameTelnet, $this->passwordTelnet, 23);

        $nokiaTelnet->startRecordingCommands(
            description: 'Provision Router-Nokia',
            ponInterface: $this->ponInterface,
            interface: null,
            serial: $this->serialALCL
        );

        $ontIndex = $nokiaTelnet->getNextOntIndex($this->ponInterface);
        $newInterface = $this->ponInterface.'/'.$ontIndex;

        $nokiaTelnet->disconnect();

        $nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $nokiaTL1->interfaces([$newInterface]);

        $entOntConfig = new EntOntConfig(
            desc1: $this->pppoeUsername,
            desc2: $this->pppoeUsername,
            serNum: $this->serialALCL,
            swVerPlnd: 'AUTO',
            opticsHist: 'ENABLE',
            plndCfgFile1: 'AUTO',
            dlCfgFile1: 'AUTO',
            voipAllowed: 'VEIP'
        );

        $provisionedOnts = $nokiaTL1->provisionOnts($entOntConfig);

        expect($provisionedOnts->first()->allCommandsSuccessful())->toBeTrue();

        $edOntConfig = new EdOntConfig();

        $editedOnts = $nokiaTL1->editProvisionedOnts($edOntConfig);

        expect($editedOnts->first()->allCommandsSuccessful())->toBeTrue();

        $entOntCardConfig = new EntOntCardConfig(
            planCardType: 'VEIP',
            plndNumDataPorts: 1,
            plndNumVoicePorts: 0,
            ontCardHolderSlot: 14
        );

        $entOntsCard = $nokiaTL1->planOntsCard($entOntCardConfig);

        expect($entOntsCard->first()->allCommandsSuccessful())->toBeTrue();

        $entLogPortConfig = new EntLogPortConfig(ontSlot: 14, ontPort: 1);

        $entOntsLogicalPortLT = $nokiaTL1->createLogicalPortOnLT($entLogPortConfig);

        expect($entOntsLogicalPortLT->first()->allCommandsSuccessful())->toBeTrue();

        $edOntVeipConfig = new EdOntVeipConfig(ontSlot: 14, ontPort: 1);

        $editedOnts = $nokiaTL1->editVeipOnts($edOntVeipConfig);

        expect($editedOnts->first()->allCommandsSuccessful())->toBeTrue();

        $qosUsQueueConfig = new QosUsQueueConfig(
            ontSlot: 14,
            ontPort: 1,
            queue: 0,
            usbwProfName: 'HSI_1G_UP'
        );

        $configuredOnts = $nokiaTL1->configureUpstreamQueue($qosUsQueueConfig);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $vlanPortConfig = new VlanPortConfig(
            ontSlot: 14,
            ontPort: 1,
            maxNUcMacAdr: 4,
            cmitMaxNumMacAddr: 1
        );

        $configuredOnts = $nokiaTL1->boundBridgePortToVlan($vlanPortConfig);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $vlanPortConfig = new VlanEgPortConfig(
            ontSlot: 14,
            ontPort: 1,
            svLan: 0,
            cvLan: 110,
            portTransMode: 'SINGLETAGGED',
        );

        $configuredOnts = $nokiaTL1->addEgressPortToVlan($vlanPortConfig);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $configuredOnts = $nokiaTL1->configureTr069Vlan(110, 1);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $configuredOnts = $nokiaTL1->configureTr069Pppoe('teste_onu_mk2', '1234', 2, 3);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $configuredOnts = $nokiaTL1->configureTr069Wifi2_4Ghz('Wifi-Nokia-2.4Ghz', '12345678', 4, 5);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $configuredOnts = $nokiaTL1->configureTr069Wifi5Ghz('Wifi-Nokia-5Ghz', '1234', 6, 7);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $configuredOnts = $nokiaTL1->configureTr069WebAccountPassword('ALC#FGU', 8);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $configuredOnts = $nokiaTL1->configureTr069AccountPassword('nokia123', 9);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $configuredOnts = $nokiaTL1->configureTr069DNS('186.224.0.18\,186.224.0.20', 12, 13, 14);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $commandBatchResult = $nokiaTL1->stopRecordingCommands();

        expect($commandBatchResult->allCommandsSuccessful())->toBeTrue();
    });
});

describe('Nokia Complete Provision and Configuration on ONTs - Bridge Chima', function () {
    it('can realize a complete provision and configuration', function () {
        $this->nokiaTelnet = Nokia::connectTelnet($this->ipOlt, $this->usernameTelnet, $this->passwordTelnet, 23);

        $this->fiberhome->startRecordingCommands(
            description: 'Provision Bridge-Chima',
            ponInterface: $this->ponInterface,
            interface: null,
            serial: $this->serialCMSZ
        );

        $ontIndex = $this->nokiaTelnet->getNextOntIndex($this->ponInterface);
        $newInterface = $this->ponInterface.'/'.$ontIndex;

        $this->nokiaTelnet->disconnect();

        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces([$newInterface]);

        $entOntConfig = new EntOntConfig(
            desc1: $this->pppoeUsername,
            desc2: $this->pppoeUsername,
            serNum: $this->serialCMSZ,
            swVerPlnd: 'DISABLED',
        );

        $provisionedOnts = $this->nokiaTL1->provisionOnts($entOntConfig);

        expect($provisionedOnts->first()->allCommandsSuccessful())->toBeTrue();

        $edOntConfig = new EdOntConfig();

        $editedOnts = $this->nokiaTL1->editProvisionedOnts($edOntConfig);

        expect($editedOnts->first()->allCommandsSuccessful())->toBeTrue();

        $entOntCardConfig = new EntOntCardConfig(
            planCardType: '10_100BASET',
            plndNumDataPorts: 1,
            plndNumVoicePorts: 0,
            ontCardHolderSlot: 1
        );

        $entOntsCard = $this->nokiaTL1->planOntsCard($entOntCardConfig);

        expect($entOntsCard->first()->allCommandsSuccessful())->toBeTrue();

        $entLogPortConfig = new EntLogPortConfig(ontSlot: 1, ontPort: 1);

        $entOntsLogicalPortLT = $this->nokiaTL1->createLogicalPortOnLT($entLogPortConfig);

        expect($entOntsLogicalPortLT->first()->allCommandsSuccessful())->toBeTrue();

        $edOntVeipConfig = new EdOntVeipConfig(ontSlot: 1, ontPort: 1);

        $editedOnts = $this->nokiaTL1->editVeipOnts($edOntVeipConfig);

        expect($editedOnts->first()->allCommandsSuccessful())->toBeTrue();

        $qosUsQueueConfig = new QosUsQueueConfig(
            ontSlot: 1,
            ontPort: 1,
            queue: 0,
            usbwProfName: 'HSI_1G_UP'
        );

        $configuredOnts = $this->nokiaTL1->configureUpstreamQueue($qosUsQueueConfig);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $vlanPortConfig = new VlanPortConfig(
            ontSlot: 1,
            ontPort: 1,
            maxNUcMacAdr: 32,
            cmitMaxNumMacAddr: 1
        );

        $configuredOnts = $this->nokiaTL1->boundBridgePortToVlan($vlanPortConfig);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $vlanPortConfig = new VlanEgPortConfig(
            ontSlot: 1,
            ontPort: 1,
            svLan: 0,
            cvLan: 110,
            portTransMode: 'UNTAGGED',
        );

        $configuredOnts = $this->nokiaTL1->addEgressPortToVlan($vlanPortConfig);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $vlanPortConfig = new VlanPortConfig(
            ontSlot: 1,
            ontPort: 1,
            maxNUcMacAdr: null,
            cmitMaxNumMacAddr: null,
            defaultCvLan: 110
        );

        $configuredOnts = $this->nokiaTL1->boundBridgePortToVlan($vlanPortConfig);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $commandBatchResult = $this->nokiaTL1->stopRecordingCommands();

        expect($commandBatchResult->allCommandsSuccessful())->toBeTrue();
    });
});
