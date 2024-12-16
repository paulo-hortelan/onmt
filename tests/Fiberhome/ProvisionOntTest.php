<?php

use Illuminate\Support\Collection;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\LanConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\VeipConfig;
use PauloHortelan\Onmt\DTOs\Fiberhome\AN551604\WanConfig;
use PauloHortelan\Onmt\Facades\Fiberhome;
use PauloHortelan\Onmt\Models\CommandResultBatch;

uses()->group('Fiberhome');

beforeEach(function () {
    $ipOlt = env('FIBERHOME_OLT_IP');
    $ipServer = env('FIBERHOME_IP_SERVER');
    $username = env('FIBERHOME_OLT_USERNAME_TL1');
    $password = env('FIBERHOME_OLT_PASSWORD_TL1');

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

    $this->fiberhome = Fiberhome::timeout(5, 10)->connectTL1($ipOlt, $username, $password, 3337, $ipServer);

});

describe('Fiberhome Provision Onts Router-Nokia', function () {
    it('can provision onts', function () {
        $this->fiberhome->interfaces([$this->interfaceALCL])->serials([$this->serialALCL]);

        $veipConfig = new VeipConfig(
            serviceId: 1,
            cVlanId: 110,
            serviceModelProfile: 'AonetVEIP',
            serviceType: 'DATA',
        );

        $provisionedOnts = $this->fiberhome->provisionRouterVeipOnts($this->ontTypeALCL, $this->pppoeUsername, $this->portInterfaceALCL, $veipConfig);

        var_dump($provisionedOnts->toArray());

        expect($provisionedOnts)->toBeInstanceOf(Collection::class);

        $provisionedOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });

    });
})->only();

describe('Fiberhome Provision Onts Router-Fiberhome', function () {
    it('can provision onts', function () {
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

        $provisionedOnts = $this->fiberhome->provisionRouterWanOnts($this->ontTypeFHTT, $this->pppoeUsername, $WanConfig);

        expect($provisionedOnts)->toBeInstanceOf(Collection::class);

        $provisionedOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });
})->skip();

describe('Fiberhome Provision Onts Bridge-Fiberhome', function () {
    it('can provision onts', function () {
        $this->fiberhome->interfaces([$this->interfaceCMSZ])->serials([$this->serialCMSZ]);

        $LanConfig = new LanConfig(
            cVlan: 110,
            cCos: 0,
        );

        $provisionedOnts = $this->fiberhome->provisionBridgeOnts($this->ontTypeCMSZ, $this->pppoeUsername, $this->portInterfaceCMSZ, $LanConfig);

        expect($provisionedOnts)->toBeInstanceOf(Collection::class);

        $provisionedOnts->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });
})->skip();
