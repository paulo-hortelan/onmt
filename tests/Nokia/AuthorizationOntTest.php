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
    // $this->serial2 = env('NOKIA_SERIAL_2');

    $this->interfaceALCL = env('NOKIA_INTERFACE_ALCL');
    // $this->interface2 = env('NOKIA_INTERFACE_2');

    $this->pppoeUsername = env('NOKIA_PPPOE_USERNAME');
    $this->pppoePassword = env('NOKIA_PPPOE_PASSWORD');

    $this->ponInterfaceALCL = env('NOKIA_PON_INTERFACE_ALCL');
});

describe('Nokia Authorize ONT\'s - Router Nokia', function () {
    it('can get next ont index', function () {
        $this->nokiaTelnet = Nokia::connectTelnet($this->ipOlt, $this->usernameTelnet, $this->passwordTelnet, 23);

        $ontIndex = $this->nokiaTelnet->getNextOntIndex($this->ponInterfaceALCL);

        expect($ontIndex)->toBeInt();
        expect($ontIndex)->toBeGreaterThan(0);

        $newInterface = $this->ponInterfaceALCL.'/'.$ontIndex;

        expect($newInterface)->toBeString();

        var_dump($newInterface);
    })->skip();

    it('can provision onts', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        // $this->nokiaTL1->enableDebug();

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

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

        $provisionedOnts = $this->nokiaTL1->provisionOnts($entOntConfig);

        var_dump($provisionedOnts);

        expect($provisionedOnts)->toBeArray();
        expect($provisionedOnts[0]['success'])->toBeTrue();
    })->skip();

    it('can edit provisioned onts', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        // $this->nokiaTL1->enableDebug();

        $edOntConfig = new EdOntConfig;

        $editedOnts = $this->nokiaTL1->editProvisionedOnts($edOntConfig);

        var_dump($editedOnts);

        expect($editedOnts)->toBeArray();
        expect($editedOnts[0]['success'])->toBeTrue();
    })->skip();

    it('can plans a new ont card', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $entOntCardConfig = new EntOntCardConfig(
            planCardType: 'VEIP',
            plndNumDataPorts: 1,
            plndNumVoicePorts: 0
        );

        $entOntsCard = $this->nokiaTL1->planOntCard($entOntCardConfig);

        var_dump($entOntsCard);

        expect($entOntsCard)->toBeArray();
        expect($entOntsCard[0]['success'])->toBeTrue();
    })->skip();

    it('can create a logical port on an lt', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $entLogPortConfig = new EntLogPortConfig;

        $entOntsLogicalPortLT = $this->nokiaTL1->createLogicalPortOnLT($entLogPortConfig);

        var_dump($entOntsLogicalPortLT);

        expect($entOntsLogicalPortLT)->toBeArray();
        expect($entOntsLogicalPortLT[0]['success'])->toBeTrue();
    })->skip();

    it('can edit onts veip', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $edOntVeipConfig = new EdOntVeipConfig;

        $editedOnts = $this->nokiaTL1->editVeipOnts($edOntVeipConfig);

        var_dump($editedOnts);

        expect($editedOnts)->toBeArray();
        expect($editedOnts[0]['success'])->toBeTrue();
    })->skip();

    it('can configure upstream queue', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $qosUsQueueConfig = new QosUsQueueConfig(usbwProfName: 'HSI_1G_UP');

        $configuredOnts = $this->nokiaTL1->configureUpstreamQueue($qosUsQueueConfig);

        var_dump($configuredOnts);

        expect($configuredOnts)->toBeArray();
        expect($configuredOnts[0]['success'])->toBeTrue();
    })->skip();

    it('can bound a bridge port to the vlan', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $vlanPortConfig = new VlanPortConfig(
            maxNUcMacAdr: 4,
            cmitMaxNumMacAddr: 1
        );

        $configuredOnts = $this->nokiaTL1->boundBridgePortToVlan($vlanPortConfig);

        var_dump($configuredOnts);

        expect($configuredOnts)->toBeArray();
        expect($configuredOnts[0]['success'])->toBeTrue();
    })->skip();

    it('can add a egress port to the vlan', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        // $this->nokiaTL1->enableDebug();

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $vlanPortConfig = new VlanEgPortConfig(
            svLan: 0,
            cvLan: 110,
            portTransMode: 'SINGLETAGGED',
        );

        $configuredOnts = $this->nokiaTL1->addEgressPortToVlan($vlanPortConfig);

        var_dump($configuredOnts);

        expect($configuredOnts)->toBeArray();
        expect($configuredOnts[0]['success'])->toBeTrue();
    })->skip();
});

describe('Nokia Configure PPPOE and VLAN on ONT\'s - Router Nokia', function () {
    it('can configure vlan', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $configureOnts = $this->nokiaTL1->configureTr069Vlan(110, 1);

        var_dump($configureOnts);

        expect($configureOnts)->toBeArray();
        expect($configureOnts[0]['success'])->toBeTrue();
    })->skip();

    it('can configure pppoe username and password', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $configsResponse = $this->nokiaTL1->configureTr069Pppoe('teste_onu_mk2', '1234', 2, 3);

        var_dump($configsResponse);

        expect($configsResponse)->toBeArray();
        expect($configsResponse[0][0]['success'])->toBeTrue();
        expect($configsResponse[1][0]['success'])->toBeTrue();
    })->skip();
});

describe('Nokia Configure WIFI on ONT\'s - Router Nokia', function () {
    it('can configure 2.4Ghz', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $configsResponse = $this->nokiaTL1->configureTr069Wifi2_4Ghz(
            'Wifi-Nokia-2.4Ghz',
            '1234',
            4,
            5
        );

        var_dump($configsResponse);

        expect($configsResponse)->toBeArray();
        expect($configsResponse[0][0]['success'])->toBeTrue();
        expect($configsResponse[1][0]['success'])->toBeTrue();
    })->skip();

    it('can configure 5Ghz', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $configsResponse = $this->nokiaTL1->configureTr069Wifi5Ghz(
            'Wifi-Nokia-5Ghz',
            '1234',
            6,
            7
        );

        var_dump($configsResponse);

        expect($configsResponse)->toBeArray();
        expect($configsResponse[0][0]['success'])->toBeTrue();
        expect($configsResponse[1][0]['success'])->toBeTrue();
    })->skip();
});

describe('Nokia Configure Web Account on ONT\'s - Router Nokia', function () {
    it('can configure 2.4Ghz', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $configsResponse = $this->nokiaTL1->configureTr069Wifi2_4Ghz(
            'Wifi-Nokia-2.4Ghz',
            '1234',
            4,
            5
        );

        var_dump($configsResponse);

        expect($configsResponse)->toBeArray();
        expect($configsResponse[0][0]['success'])->toBeTrue();
        expect($configsResponse[1][0]['success'])->toBeTrue();
    })->skip();

    it('can configure 5Ghz', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $configsResponse = $this->nokiaTL1->configureTr069Wifi5Ghz(
            'Wifi-Nokia-5Ghz',
            '1234',
            6,
            7
        );

        var_dump($configsResponse);

        expect($configsResponse)->toBeArray();
        expect($configsResponse[0][0]['success'])->toBeTrue();
        expect($configsResponse[1][0]['success'])->toBeTrue();
    })->skip();
});

describe('Nokia Remove ONT\'s', function () {
    it('can remove onts', function () {
        $this->nokiaTelnet = Nokia::connectTelnet($this->ipOlt, $this->usernameTelnet, $this->passwordTelnet, 23);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $removedOnts = $this->nokiaTelnet->removeOnts();

        var_dump($removedOnts);

        expect($removedOnts)->toBeArray();
        expect($removedOnts[0]['success'])->toBeTrue();
    })->skip();
});
