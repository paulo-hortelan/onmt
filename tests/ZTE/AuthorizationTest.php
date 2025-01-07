<?php

use Illuminate\Support\Collection;
use PauloHortelan\Onmt\DTOs\ZTE\C300\GemportConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\ServiceConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\ServicePortConfig;
use PauloHortelan\Onmt\DTOs\ZTE\C300\VlanPortConfig;
use PauloHortelan\Onmt\Facades\ZTE;
use PauloHortelan\Onmt\Models\CommandResultBatch;

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
    $this->serialALCLC600 = env('ZTE_C600_SERIAL_ALCL');
    $this->serialCMSZC600 = env('ZTE_C600_SERIAL_CMSZ');
    $this->interfaceALCLC300 = env('ZTE_C300_INTERFACE_ALCL');
    $this->interfaceCMSZC300 = env('ZTE_C300_INTERFACE_CMSZ');
    $this->interfaceALCLC600 = env('ZTE_C600_INTERFACE_ALCL');
    $this->interfaceCMSZC600 = env('ZTE_C600_INTERFACE_CMSZ');

    $this->ponInterfaceALCLC300 = env('ZTE_C300_PON_INTERFACE_ALCL');
    $this->ponInterfaceCMSZC300 = env('ZTE_C300_PON_INTERFACE_CMSZ');
    $this->ponInterfaceALCLC600 = env('ZTE_C600_PON_INTERFACE_ALCL');
    $this->ponInterfaceCMSZC600 = env('ZTE_C600_PON_INTERFACE_CMSZ');
});

describe('ZTE C300 - Authorize/Register ONTs', function () {
    it('can provision CMSZ', function () {
        $zte = ZTE::connectTelnet($this->ipServerC300, $this->usernameTelnetC300, $this->passwordTelnetC300, 23);

        $zte->serials([$this->serialCMSZC300]);

        $ontIndex = $zte->getNextOntIndex($this->ponInterfaceCMSZC300);

        $authorizedOnts = $zte->provisionOnts($this->ponInterfaceCMSZC300, $ontIndex, 'BRIDGE');

        $zte->disconnect();

        expect($authorizedOnts)->toBeInstanceOf(Collection::class);

        $authorizedOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can provision ALCL', function () {
        $zte = ZTE::connectTelnet($this->ipServerC300, $this->usernameTelnetC300, $this->passwordTelnetC300, 23);

        $zte->serials([$this->serialALCLC300]);

        $ontIndex = $zte->getNextOntIndex($this->ponInterfaceALCLC300);

        $authorizedOnts = $zte->provisionOnts($this->ponInterfaceALCLC300, $ontIndex, 'ROUTER');

        $zte->disconnect();

        expect($authorizedOnts)->toBeInstanceOf(Collection::class);

        $authorizedOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });
});

describe('ZTE C600 - Authorize/Register ONTs', function () {
    it('can provision CMSZ', function () {
        $zte = ZTE::connectTelnet(
            ipOlt: $this->ipServerC600,
            username: $this->usernameTelnetC600,
            password: $this->passwordTelnetC600,
            port: 23,
            model: 'C600'
        );

        $zte->serials([$this->serialCMSZC600]);

        $ontIndex = $zte->getNextOntIndex($this->ponInterfaceCMSZC600);

        $authorizedOnts = $zte->provisionOnts($this->ponInterfaceCMSZC600, $ontIndex, 'BRIDGE');

        dump($authorizedOnts->toArray());
        $zte->disconnect();

        expect($authorizedOnts)->toBeInstanceOf(Collection::class);

        $authorizedOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can provision ALCL', function () {
        $zte = ZTE::connectTelnet(
            ipOlt: $this->ipServerC600,
            username: $this->usernameTelnetC600,
            password: $this->passwordTelnetC600,
            port: 23,
            model: 'C600'
        );

        $zte->serials([$this->serialALCLC600]);

        $ontIndex = $zte->getNextOntIndex($this->ponInterfaceALCLC600);

        $authorizedOnts = $zte->provisionOnts($this->ponInterfaceALCLC600, $ontIndex, 'ROUTER');

        $zte->disconnect();

        expect($authorizedOnts)->toBeInstanceOf(Collection::class);

        $authorizedOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });
});

describe('ZTE C300 - Remove ONTs', function () {
    it('can remove', function () {
        $zte = ZTE::connectTelnet($this->ipServerC300, $this->usernameTelnetC300, $this->passwordTelnetC300, 23);

        $zte->interfaces([$this->interfaceCMSZC300, $this->interfaceALCLC300]);

        $removedOnts = $zte->removeOnts();

        dump($removedOnts->toArray());

        $zte->disconnect();

        expect($removedOnts)->toBeInstanceOf(Collection::class);

        $removedOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });
});

describe('ZTE C600 - Remove ONTs', function () {
    it('can remove', function () {
        $zte = ZTE::connectTelnet(
            ipOlt: $this->ipServerC600,
            username: $this->usernameTelnetC600,
            password: $this->passwordTelnetC600,
            port: 23,
            model: 'C600'
        );

        $zte->interfaces([$this->interfaceCMSZC600, $this->interfaceALCLC600]);

        $removedOnts = $zte->removeOnts();

        dump($removedOnts->toArray());

        $zte->disconnect();

        expect($removedOnts)->toBeInstanceOf(Collection::class);

        $removedOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });
});

describe('ZTE C300 - Configure ONTs CMSZ', function () {
    it('can set name', function () {
        $zte = ZTE::connectTelnet($this->ipServerC300, $this->usernameTelnetC300, $this->passwordTelnetC300, 23);

        $zte->interfaces([$this->interfaceCMSZC300]);

        $configuredOnts = $zte->setOntsName('name-test');

        dump($configuredOnts->toArray());

        $zte->disconnect();

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can set description', function () {
        $zte = ZTE::connectTelnet($this->ipServerC300, $this->usernameTelnetC300, $this->passwordTelnetC300, 23);

        $zte->interfaces([$this->interfaceCMSZC300]);

        $configuredOnts = $zte->setOntsDescription('description-test');

        dump($configuredOnts->toArray());

        $zte->disconnect();

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure tcont', function () {
        $zte = ZTE::connectTelnet($this->ipServerC300, $this->usernameTelnetC300, $this->passwordTelnetC300, 23);

        $zte->interfaces([$this->interfaceCMSZC300]);

        $configuredOnts = $zte->configureTCont(1, 'SMARTOLT-1G-UP');

        dump($configuredOnts->toArray());

        $zte->disconnect();

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure gemport', function () {
        $zte = ZTE::connectTelnet($this->ipServerC300, $this->usernameTelnetC300, $this->passwordTelnetC300, 23);

        $zte->interfaces([$this->interfaceCMSZC300]);

        $gemportConfig = new GemportConfig(
            gemportId: 1,
            tcontId: 1
        );

        $configuredOnts = $zte->configureGemport($gemportConfig, 'interface-onu');

        dump($configuredOnts->toArray());

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });

        $gemportConfig = new GemportConfig(
            gemportId: 1,
            tcontId: null,
            flowId: null,
            upstreamProfile: null,
            downstreamProfile: 'SMARTOLT-1G-DOWN'
        );

        $configuredOnts = $zte->configureGemport($gemportConfig, 'interface-onu');

        dump($configuredOnts->toArray());

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure serviceport', function () {
        $zte = ZTE::connectTelnet($this->ipServerC300, $this->usernameTelnetC300, $this->passwordTelnetC300, 23);

        $zte->interfaces([$this->interfaceCMSZC300]);

        $servicePortConfig = new ServicePortConfig(
            servicePortId: 1,
            vport: 1,
            userVlan: 110,
            vlan: 110
        );

        $configuredOnts = $zte->configureServicePort($servicePortConfig);

        dump($configuredOnts->toArray());

        $zte->disconnect();

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure service', function () {
        $zte = ZTE::connectTelnet($this->ipServerC300, $this->usernameTelnetC300, $this->passwordTelnetC300, 23);

        $zte->interfaces([$this->interfaceCMSZC300]);

        $serviceConfig = new ServiceConfig(
            serviceName: 'internet',
            gemportId: 1,
            vlan: 110,
        );

        $configuredOnts = $zte->configureService($serviceConfig);

        dump($configuredOnts->toArray());

        $zte->disconnect();

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure vlan port', function () {
        $zte = ZTE::connectTelnet($this->ipServerC300, $this->usernameTelnetC300, $this->passwordTelnetC300, 23);

        $zte->interfaces([$this->interfaceCMSZC300]);

        $vlanPortConfig = new VlanPortConfig(
            portName: 'eth_0/1',
            mode: 'tag',
            tag: 'vlan',
            vlan: 110
        );

        $configuredOnts = $zte->configureVlanPort($vlanPortConfig);

        dump($configuredOnts->toArray());

        $zte->disconnect();

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

describe('ZTE C600 - Configure ONTs ALCL', function () {
    it('can set name', function () {
        $zte = ZTE::connectTelnet(
            ipOlt: $this->ipServerC600,
            username: $this->usernameTelnetC600,
            password: $this->passwordTelnetC600,
            port: 23,
            model: 'C600'
        );

        $zte->interfaces([$this->interfaceALCLC600]);

        $configuredOnts = $zte->setOntsName('name-test-router');

        dump($configuredOnts->toArray());

        $zte->disconnect();

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can set description', function () {
        $zte = ZTE::connectTelnet(
            ipOlt: $this->ipServerC600,
            username: $this->usernameTelnetC600,
            password: $this->passwordTelnetC600,
            port: 23,
            model: 'C600'
        );

        $zte->interfaces([$this->interfaceALCLC600]);

        $configuredOnts = $zte->setOntsDescription('description-test-router');

        dump($configuredOnts->toArray());

        $zte->disconnect();

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure tcont', function () {
        $zte = ZTE::connectTelnet(
            ipOlt: $this->ipServerC600,
            username: $this->usernameTelnetC600,
            password: $this->passwordTelnetC600,
            port: 23,
            model: 'C600'
        );

        $zte->interfaces([$this->interfaceALCLC600]);

        $configuredOnts = $zte->configureTCont(1, '1G');

        dump($configuredOnts->toArray());

        $zte->disconnect();

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure gemport on interface-onu', function () {
        $zte = ZTE::connectTelnet(
            ipOlt: $this->ipServerC600,
            username: $this->usernameTelnetC600,
            password: $this->passwordTelnetC600,
            port: 23,
            model: 'C600'
        );

        $zte->interfaces([$this->interfaceALCLC600]);

        $gemportConfig = new GemportConfig(
            gemportId: 1,
            tcontId: 1
        );

        $configuredOnts = $zte->configureGemport($gemportConfig, 'interface-onu');

        dump($configuredOnts->toArray());

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure service', function () {
        $zte = ZTE::connectTelnet(
            ipOlt: $this->ipServerC600,
            username: $this->usernameTelnetC600,
            password: $this->passwordTelnetC600,
            port: 23,
            model: 'C600'
        );

        $zte->interfaces([$this->interfaceALCLC600]);

        $servicePortConfig = new ServiceConfig(
            serviceName: 1,
            gemportId: 1,
            veip: 1,
            vlan: 110
        );

        $configuredOnts = $zte->configureService($servicePortConfig);

        dump($configuredOnts->toArray());

        $zte->disconnect();

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure vlan port', function () {
        $zte = ZTE::connectTelnet(
            ipOlt: $this->ipServerC600,
            username: $this->usernameTelnetC600,
            password: $this->passwordTelnetC600,
            port: 23,
            model: 'C600'
        );

        $zte->interfaces([$this->interfaceALCLC600]);

        $vlanPortConfig = new VlanPortConfig(
            portName: 'eth_0/1',
            mode: 'tag',
            tag: 'vlan',
            vlan: 110
        );

        $configuredOnts = $zte->configureVlanPort($vlanPortConfig);

        dump($configuredOnts->toArray());

        $zte->disconnect();

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure serviceport', function () {
        $zte = ZTE::connectTelnet(
            ipOlt: $this->ipServerC600,
            username: $this->usernameTelnetC600,
            password: $this->passwordTelnetC600,
            port: 23,
            model: 'C600'
        );

        $zte->interfaces([$this->interfaceALCLC600]);

        $servicePortConfig = new ServicePortConfig(
            servicePortId: 1,
            userVlan: 110,
            vlan: 110
        );

        $configuredOnts = $zte->configureServicePort($servicePortConfig, 1);

        dump($configuredOnts->toArray());

        $zte->disconnect();

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

describe('ZTE C600 - Configure ONTs CMSZ', function () {
    it('can set name', function () {
        $zte = ZTE::connectTelnet(
            ipOlt: $this->ipServerC600,
            username: $this->usernameTelnetC600,
            password: $this->passwordTelnetC600,
            port: 23,
            model: 'C600'
        );

        $zte->interfaces([$this->interfaceALCLC600]);

        $configuredOnts = $zte->setOntsName('name-test-router');

        dump($configuredOnts->toArray());

        $zte->disconnect();

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can set description', function () {
        $zte = ZTE::connectTelnet(
            ipOlt: $this->ipServerC600,
            username: $this->usernameTelnetC600,
            password: $this->passwordTelnetC600,
            port: 23,
            model: 'C600'
        );

        $zte->interfaces([$this->interfaceALCLC600]);

        $configuredOnts = $zte->setOntsDescription('description-test-router');

        dump($configuredOnts->toArray());

        $zte->disconnect();

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure tcont', function () {
        $zte = ZTE::connectTelnet(
            ipOlt: $this->ipServerC600,
            username: $this->usernameTelnetC600,
            password: $this->passwordTelnetC600,
            port: 23,
            model: 'C600'
        );

        $zte->interfaces([$this->interfaceALCLC600]);

        $configuredOnts = $zte->configureTCont(1, '1G');

        dump($configuredOnts->toArray());

        $zte->disconnect();

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure gemport on interface-onu', function () {
        $zte = ZTE::connectTelnet(
            ipOlt: $this->ipServerC600,
            username: $this->usernameTelnetC600,
            password: $this->passwordTelnetC600,
            port: 23,
            model: 'C600'
        );

        $zte->interfaces([$this->interfaceALCLC600]);

        $gemportConfig = new GemportConfig(
            gemportId: 1,
            tcontId: 1
        );

        $configuredOnts = $zte->configureGemport($gemportConfig, 'interface-onu');

        dump($configuredOnts->toArray());

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure service', function () {
        $zte = ZTE::connectTelnet(
            ipOlt: $this->ipServerC600,
            username: $this->usernameTelnetC600,
            password: $this->passwordTelnetC600,
            port: 23,
            model: 'C600'
        );

        $zte->interfaces([$this->interfaceALCLC600]);

        $servicePortConfig = new ServiceConfig(
            serviceName: 1,
            gemportId: 1,
            veip: 1,
            vlan: 110
        );

        $configuredOnts = $zte->configureService($servicePortConfig);

        dump($configuredOnts->toArray());

        $zte->disconnect();

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure vlan port', function () {
        $zte = ZTE::connectTelnet(
            ipOlt: $this->ipServerC600,
            username: $this->usernameTelnetC600,
            password: $this->passwordTelnetC600,
            port: 23,
            model: 'C600'
        );

        $zte->interfaces([$this->interfaceALCLC600]);

        $vlanPortConfig = new VlanPortConfig(
            portName: 'eth_0/1',
            mode: 'tag',
            tag: 'vlan',
            vlan: 110
        );

        $configuredOnts = $zte->configureVlanPort($vlanPortConfig);

        dump($configuredOnts->toArray());

        $zte->disconnect();

        expect($configuredOnts)->toBeInstanceOf(Collection::class);

        $configuredOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('can configure serviceport', function () {
        $zte = ZTE::connectTelnet(
            ipOlt: $this->ipServerC600,
            username: $this->usernameTelnetC600,
            password: $this->passwordTelnetC600,
            port: 23,
            model: 'C600'
        );

        $zte->interfaces([$this->interfaceALCLC600]);

        $servicePortConfig = new ServicePortConfig(
            servicePortId: 1,
            userVlan: 110,
            vlan: 110
        );

        $configuredOnts = $zte->configureServicePort($servicePortConfig, 1);

        dump($configuredOnts->toArray());

        $zte->disconnect();

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
