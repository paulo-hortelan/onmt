<?php

use PauloHortelan\Onmt\DTOs\ZTE\C300\FlowConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\FlowModeConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\GemportConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\ServiceConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\ServicePortConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\SwitchportBindConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\VlanFilterConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\VlanFilterModeConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\VlanPortConfig;
use PauloHortelan\Onmt\Facades\ZTE;

uses()->group('ZTE');

beforeEach(function () {
    $this->ipServerC300 = env('ZTE_C300_OLT_IP');
    $this->usernameTelnetC300 = env('ZTE_C300_OLT_USERNAME_TELNET');
    $this->passwordTelnetC300 = env('ZTE_C300_OLT_PASSWORD_TELNET');

    $this->ipServerC600 = env('ZTE_C600_OLT_IP');
    $this->usernameTelnetC600 = env('ZTE_C600_OLT_USERNAME_TELNET');
    $this->passwordTelnetC600 = env('ZTE_C600_OLT_PASSWORD_TELNET');

    $this->serialALCLC300 = env('ZTE_C300_SERIAL_ALCL');
    $this->serialCMSZC300 = env('ZTE_C300_SERIAL_CMSZ');

    $this->ponInterfaceALCLC300 = env('ZTE_C300_PON_INTERFACE_ALCL');
    $this->ponInterfaceCMSZC300 = env('ZTE_C300_PON_INTERFACE_CMSZ');
});

describe('ZTE C300 - Complete Provision and Configuration ONTs - Bridge Chima', function () {
    it('can realize a complete provision and configuration', function () {
        $zte = ZTE::connectTelnet($this->ipServerC300, $this->usernameTelnetC300, $this->passwordTelnetC300, 23);

        $zte->serials([$this->serialCMSZC300]);

        $zte->startRecordingCommands(
            description: 'Provision Bridge-Chima',
            ponInterface: $this->ponInterfaceCMSZC300,
            interface: null,
            serial: $this->serialCMSZC300
        );

        $ontIndex = $zte->getNextOntIndex($this->ponInterfaceCMSZC300);

        $authorizedOnts = $zte->provisionOnts($this->ponInterfaceCMSZC300, $ontIndex, 'BRIDGE');

        expect($authorizedOnts->first()->allCommandsSuccessful())->toBeTrue();

        $interface = $this->ponInterfaceCMSZC300.':'.$ontIndex;

        $zte->interfaces([$interface]);

        $configuredOnts = $zte->setOntsName('test');

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $configuredOnts = $zte->setOntsDescription('test');

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $configuredOnts = $zte->configureTCont(1, 'SMARTOLT-1G-UP');

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $gemportConfig = new GemportConfig(
            gemportId: 1,
            tcontId: 1
        );

        $configuredOnts = $zte->configureGemport($gemportConfig, 'interface-onu');

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $servicePortConfig = new ServicePortConfig(
            servicePortId: 1,
            vport: 1,
            userVlan: 110,
            vlan: 110
        );

        $configuredOnts = $zte->configureServicePort($servicePortConfig);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $serviceConfig = new ServiceConfig(
            serviceName: 'internet',
            gemportId: 1,
            vlan: 110,
        );

        $configuredOnts = $zte->configureService($serviceConfig);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $vlanPortConfig = new VlanPortConfig(
            portName: 'eth_0/1',
            mode: 'tag',
            tag: 'vlan',
            vlan: 110
        );

        $configuredOnts = $zte->configureVlanPort($vlanPortConfig);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $commandBatchResult = $zte->stopRecordingCommands();

        $zte->disconnect();

        dump($commandBatchResult->toArray());

        expect($commandBatchResult->first()->allCommandsSuccessful())->toBeTrue();
    });
});

describe('ZTE C300 - Complete Provision and Configuration ONTs - Router Nokia', function () {
    it('can realize a complete provision and configuration', function () {
        $zte = ZTE::connectTelnet($this->ipServerC300, $this->usernameTelnetC300, $this->passwordTelnetC300, 23);

        $zte->serials([$this->serialALCLC300]);

        $zte->startRecordingCommands(
            description: 'Provision Bridge-Chima',
            ponInterface: $this->ponInterfaceALCLC300,
            interface: null,
            serial: $this->serialALCLC300
        );

        $ontIndex = $zte->getNextOntIndex($this->ponInterfaceALCLC300);

        $authorizedOnts = $zte->provisionOnts($this->ponInterfaceALCLC300, $ontIndex, 'ROUTER');

        expect($authorizedOnts->first()->allCommandsSuccessful())->toBeTrue();

        $interface = $this->ponInterfaceALCLC300.':'.$ontIndex;

        $zte->interfaces([$interface]);

        $configuredOnts = $zte->setOntsName('test');

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $configuredOnts = $zte->setOntsDescription('test');

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $configuredOnts = $zte->configureTCont(1, 'SMARTOLT-1G-UP');

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $gemportConfig = new GemportConfig(
            gemportId: 1,
            tcontId: 1
        );

        $configuredOnts = $zte->configureGemport($gemportConfig, 'interface-onu');

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $servicePortConfig = new ServicePortConfig(
            servicePortId: 1,
            vport: 1,
            userVlan: 110,
            vlan: 110
        );

        $configuredOnts = $zte->configureServicePort($servicePortConfig);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $flowModeConfig = new FlowModeConfig(
            flowId: 1,
            tagFilter: 'vlan-filter',
            untagFilter: 'discard'
        );

        $configuredOnts = $zte->configureFlowMode($flowModeConfig);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $flowConfig = new FlowConfig(
            flowId: 1,
            priority: 0,
            vlan: 110
        );

        $configuredOnts = $zte->configureFlow($flowConfig);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $gemportConfig = new GemportConfig(
            gemportId: 1,
            tcontId: null,
            flowId: 1
        );

        $configuredOnts = $zte->configureGemport($gemportConfig, 'pon-onu-mng');

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $switchportBindConfig = new SwitchportBindConfig(
            switchName: 'switch_0/1',
            veip: null,
            iphost: 1
        );

        $configuredOnts = $zte->configureSwitchportBind($switchportBindConfig);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $switchportBindConfig = new SwitchportBindConfig(
            switchName: 'switch_0/1',
            veip: 1,
            iphost: null
        );

        $configuredOnts = $zte->configureSwitchportBind($switchportBindConfig);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $vlanFilterModeConfig = new VlanFilterModeConfig(
            iphost: 1,
            tagFilter: 'vlan-filter',
            untagFilter: 'discard'
        );

        $configuredOnts = $zte->configureVlanFilterMode($vlanFilterModeConfig);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $vlanFilterConfig = new VlanFilterConfig(
            iphost: 1,
            priority: 0,
            vlan: 110
        );

        $configuredOnts = $zte->configureVlanFilter($vlanFilterConfig);

        expect($configuredOnts->first()->allCommandsSuccessful())->toBeTrue();

        $commandBatchResult = $zte->stopRecordingCommands();

        $zte->disconnect();

        dump($commandBatchResult->toArray());

        expect($commandBatchResult->first()->allCommandsSuccessful())->toBeTrue();
    });
})->only();
