<?php

use Illuminate\Support\Collection;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\ConfigureBridgePort;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\ConfigureEquipmentOntInterface;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\ConfigureEquipmentOntSlot;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\ConfigureInterfacePort;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\ConfigureQosInterface;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EdOntConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EdOntVeipConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntLogPortConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntOntCardConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\EntOntConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\QosUsQueueConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\VlanEgPortConfig;
use PauloHortelan\Onmt\DTOs\Nokia\FX16\VlanPortConfig;
use PauloHortelan\Onmt\Facades\Nokia;
use PauloHortelan\Onmt\Models\CommandResultBatch;

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

describe('Nokia Authorize ONTs - TL1 - Router', function () {
    it('can get next ont index', function () {
        $this->nokiaTelnet = Nokia::connectTelnet($this->ipOlt, $this->usernameTelnet, $this->passwordTelnet, 23);

        $ontIndex = $this->nokiaTelnet->getNextOntIndex($this->ponInterface);

        expect($ontIndex)->toBeInt();
        expect($ontIndex)->toBeGreaterThan(0);

        $newInterface = $this->ponInterface.'/'.$ontIndex;

        expect($newInterface)->toBeString();
    });

    it('can provision onts', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

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

        expect($provisionedOnts)->toBeInstanceOf(Collection::class);

        $provisionedOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can edit provisioned onts', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $edOntConfig = new EdOntConfig();

        $editedOnts = $this->nokiaTL1->editProvisionedOnts($edOntConfig);

        expect($editedOnts)->toBeInstanceOf(Collection::class);

        $editedOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can plans a new ont card', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $entOntCardConfig = new EntOntCardConfig(
            planCardType: 'VEIP',
            plndNumDataPorts: 1,
            plndNumVoicePorts: 0,
            ontCardHolderSlot: 14
        );

        $plannedOntsCard = $this->nokiaTL1->planOntsCard($entOntCardConfig);

        expect($plannedOntsCard)->toBeInstanceOf(Collection::class);

        $plannedOntsCard->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can create a logical port on an lt', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $entLogPortConfig = new EntLogPortConfig(ontSlot: 14, ontPort: 1);

        $createdLogicalPortOnts = $this->nokiaTL1->createLogicalPortOnLT($entLogPortConfig);

        expect($createdLogicalPortOnts)->toBeInstanceOf(Collection::class);

        $createdLogicalPortOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can edit onts veip', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $edOntVeipConfig = new EdOntVeipConfig(
            ontSlot: 14,
            ontPort: 1
        );

        $editedOnts = $this->nokiaTL1->editVeipOnts($edOntVeipConfig);

        expect($editedOnts)->toBeInstanceOf(Collection::class);

        $editedOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure upstream queue', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $qosUsQueueConfig = new QosUsQueueConfig(
            ontSlot: 14,
            ontPort: 1,
            queue: 0,
            usbwProfName: 'HSI_1G_UP'
        );

        $configuredOnts = $this->nokiaTL1->configureUpstreamQueue($qosUsQueueConfig);

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can bound a bridge port to the vlan', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $vlanPortConfig = new VlanPortConfig(
            ontSlot: 14,
            ontPort: 1,
            maxNUcMacAdr: 4,
            cmitMaxNumMacAddr: 1
        );

        $configuredOnts = $this->nokiaTL1->boundBridgePortToVlan($vlanPortConfig);

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can add a egress port to the vlan', function () {
        $this->nokiaTL1 = Nokia::connectTL1($this->ipOlt, $this->usernameTL1, $this->passwordTL1, 1023);

        $this->nokiaTL1->interfaces(['1/1/1/1/3']);

        $vlanPortConfig = new VlanEgPortConfig(
            ontSlot: 14,
            ontPort: 1,
            svLan: 0,
            cvLan: 110,
            portTransMode: 'SINGLETAGGED',
        );

        $configuredOnts = $this->nokiaTL1->addEgressPortToVlan($vlanPortConfig);

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });
});

describe('Nokia Authorize ONTs - Telnet - Router', function () {
    it('can get next ont index', function () {
        $this->nokiaTelnet = Nokia::connectTelnet($this->ipOlt, $this->usernameTelnet, $this->passwordTelnet, 23);

        $ontIndex = $this->nokiaTelnet->getNextOntIndex($this->ponInterface);

        expect($ontIndex)->toBeInt();
        expect($ontIndex)->toBeGreaterThan(0);

        $newInterface = $this->ponInterface.'/'.$ontIndex;

        expect($newInterface)->toBeString();
    });

    it('can configure equipment ont interface', function () {
        $this->nokia = Nokia::connectTelnet($this->ipOlt, $this->usernameTelnet, $this->passwordTelnet, 23);

        $this->nokia->interfaces(['1/1/1/1/6']);

        $config = new ConfigureEquipmentOntInterface(
            swVerPlnd: 'auto',
            swDnloadVersion: 'auto',
            sernum: 'ALCLB407BB8D',
            plandCfgfile1: 'auto',
            dnloadCfgfile1: 'auto',
            desc1: 'teste_onu_mk'
        );

        $result = $this->nokia->configureInterfaceOnts($config);

        dump($result->toArray());

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure equipment ont interface admin-state up', function () {
        $this->nokia = Nokia::connectTelnet($this->ipOlt, $this->usernameTelnet, $this->passwordTelnet, 23);

        $this->nokia->interfaces(['1/1/1/1/6']);

        $result = $this->nokia->configureInterfaceAdminStateOnts('up');

        dump($result->toArray());

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure equipment ont slot', function () {
        $this->nokia = Nokia::connectTelnet($this->ipOlt, $this->usernameTelnet, $this->passwordTelnet, 23);

        $this->nokia->interfaces(['1/1/1/1/6']);

        $config = new ConfigureEquipmentOntSlot(
            ontSlot: 14,
            plannedCardType: 'veip',
            plndnumdataports: 1,
            plndnumvoiceports: 0,
            adminState: 'up',
        );

        $result = $this->nokia->configureSlotOnts($config);

        dump($result->toArray());

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure qos interface', function () {
        $this->nokia = Nokia::connectTelnet($this->ipOlt, $this->usernameTelnet, $this->passwordTelnet, 23);

        $this->nokia->interfaces(['1/1/1/1/6']);

        $config = new ConfigureQosInterface(
            qosInterface: '14/1',
            upstreamQueue: 0,
            bandwidthProfile: 'name:HSI_1G_UP',
            queue: null,
            shaperProfile: null,
        );

        $result = $this->nokia->configureQosInterfaces($config);

        dump($result->toArray());

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });

        $config = new ConfigureQosInterface(
            qosInterface: '14/1',
            upstreamQueue: null,
            bandwidthProfile: null,
            queue: 0,
            shaperProfile: 'name:HSI_1G_DOWN',
        );

        $result = $this->nokia->configureQosInterfaces($config);

        dump($result->toArray());

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure interface port', function () {
        $this->nokia = Nokia::connectTelnet($this->ipOlt, $this->usernameTelnet, $this->passwordTelnet, 23);

        $this->nokia->interfaces(['1/1/1/1/6']);

        $config = new ConfigureInterfacePort(
            interfacePort: '14/1',
            adminStatus: 'admin-up',
        );

        $result = $this->nokia->configureInterfacesPorts($config);

        dump($result->toArray());

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure bridge port', function () {
        $this->nokia = Nokia::connectTelnet($this->ipOlt, $this->usernameTelnet, $this->passwordTelnet, 23);

        $this->nokia->interfaces(['1/1/1/1/6']);

        $config = new ConfigureBridgePort(
            bridgePort: '14/1',
            maxUnicastMac: 4,
        );

        $result = $this->nokia->configureBridgePorts($config);

        dump($result->toArray());

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });

        $config = new ConfigureBridgePort(
            bridgePort: '14/1',
            vlanId: 110,
        );

        $result = $this->nokia->configureBridgePorts($config);

        dump($result->toArray());

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });

        $config = new ConfigureBridgePort(
            bridgePort: '14/1',
            pvid: 110,
        );

        $result = $this->nokia->configureBridgePorts($config);

        dump($result->toArray());

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });
});

describe('Nokia Authorize ONTs - Telnet - Bridge', function () {
    it('can get next ont index', function () {
        $this->nokiaTelnet = Nokia::connectTelnet($this->ipOlt, $this->usernameTelnet, $this->passwordTelnet, 23);

        $ontIndex = $this->nokiaTelnet->getNextOntIndex($this->ponInterface);

        expect($ontIndex)->toBeInt();
        expect($ontIndex)->toBeGreaterThan(0);

        $newInterface = $this->ponInterface.'/'.$ontIndex;

        expect($newInterface)->toBeString();
    });

    it('can configure equipment ont interface', function () {
        $this->nokia = Nokia::connectTelnet($this->ipOlt, $this->usernameTelnet, $this->passwordTelnet, 23);

        $this->nokia->interfaces(['1/1/1/1/7']);

        $config = new ConfigureEquipmentOntInterface(
            swVerPlnd: 'disabled',
            swDnloadVersion: ConfigureEquipmentOntInterface::NO,
            sernum: 'CMSZ3BC079CE',
            plandCfgfile1: 'auto',
            dnloadCfgfile1: ConfigureEquipmentOntInterface::NO,
            desc1: 'teste_onu_mk'
        );

        $result = $this->nokia->configureInterfaceOnts($config);

        dump($result->toArray());

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure equipment ont interface admin-state up', function () {
        $this->nokia = Nokia::connectTelnet($this->ipOlt, $this->usernameTelnet, $this->passwordTelnet, 23);

        $this->nokia->interfaces(['1/1/1/1/7']);

        $result = $this->nokia->configureInterfaceAdminStateOnts('up');

        dump($result->toArray());

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure equipment ont slot', function () {
        $this->nokia = Nokia::connectTelnet($this->ipOlt, $this->usernameTelnet, $this->passwordTelnet, 23);

        $this->nokia->interfaces(['1/1/1/1/7']);

        $config = new ConfigureEquipmentOntSlot(
            ontSlot: 1,
            plannedCardType: 'ethernet',
            plndnumdataports: 1,
            plndnumvoiceports: 0,
            adminState: 'up',
        );

        $result = $this->nokia->configureSlotOnts($config);

        dump($result->toArray());

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure qos interface', function () {
        $this->nokia = Nokia::connectTelnet($this->ipOlt, $this->usernameTelnet, $this->passwordTelnet, 23);

        $this->nokia->interfaces(['1/1/1/1/6']);

        $config = new ConfigureQosInterface(
            qosInterface: '1/1',
            upstreamQueue: 0,
            bandwidthProfile: 'name:HSI_1G_UP',
        );

        $result = $this->nokia->configureQosInterfaces($config);

        dump($result->toArray());

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });

        $config = new ConfigureQosInterface(
            qosInterface: '1/1',
            queue: 0,
            shaperProfile: 'name:HSI_1G_DOWN',
        );

        $result = $this->nokia->configureQosInterfaces($config);

        dump($result->toArray());

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    })->only();

    it('can configure interface port', function () {
        $this->nokia = Nokia::connectTelnet($this->ipOlt, $this->usernameTelnet, $this->passwordTelnet, 23);

        $this->nokia->interfaces(['1/1/1/1/6']);

        $config = new ConfigureInterfacePort(
            interfacePort: '1/1',
            adminStatus: 'admin-up',
        );

        $result = $this->nokia->configureInterfacesPorts($config);

        dump($result->toArray());

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure bridge port', function () {
        $this->nokia = Nokia::connectTelnet($this->ipOlt, $this->usernameTelnet, $this->passwordTelnet, 23);

        $this->nokia->interfaces(['1/1/1/1/6']);

        $config = new ConfigureBridgePort(
            bridgePort: '1/1',
            maxUnicastMac: 4,
        );

        $result = $this->nokia->configureBridgePorts($config);

        dump($result->toArray());

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });

        $config = new ConfigureBridgePort(
            bridgePort: '1/1',
            vlanId: 110,
        );

        $result = $this->nokia->configureBridgePorts($config);

        dump($result->toArray());

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });

        $config = new ConfigureBridgePort(
            bridgePort: '1/1',
            pvid: 110,
        );

        $result = $this->nokia->configureBridgePorts($config);

        dump($result->toArray());

        expect($result)->toBeInstanceOf(Collection::class);

        $result->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });
});
