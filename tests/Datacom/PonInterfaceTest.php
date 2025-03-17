<?php

use Illuminate\Support\Collection;
use PauloHortelan\Onmt\Facades\Datacom;
use PauloHortelan\Onmt\Models\CommandResultBatch;

uses()->group('Datacom');

beforeEach(function () {
    $this->ipServer = env('DATACOM_DM4612_OLT_IP');
    $this->usernameTelnet = env('DATACOM_DM4612_OLT_USERNAME_TELNET');
    $this->passwordTelnet = env('DATACOM_DM4612_OLT_PASSWORD_TELNET');

    $this->serialALCL = env('DATACOM_DM4612_SERIAL_ALCL');
    $this->ponInterfaceALCL = env('DATACOM_DM4612_PON_INTERFACE_ALCL');
});

describe('Datacom - Onts by Pon Interface - Success', function () {
    it('can get onts by pon interface', function () {
        $datacom = Datacom::connectTelnet($this->ipServer, $this->usernameTelnet, $this->passwordTelnet, 23);

        $datacom->serials([$this->serialALCL]);

        $ontInfo = $datacom->ontsByPonInterface('1/1/1');

        expect($ontInfo)->toBeInstanceOf(Collection::class);

        $ontInfo->each(function ($batch) {
            expect($batch)->toBeInstanceOf(CommandResultBatch::class);
            expect($batch->commands)->toBeInstanceOf(Collection::class);

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();

                foreach ($commandResult->result as $onu) {
                    expect($onu)->toHaveKeys([
                        'interface',
                        'onuId',
                        'serialNumber',
                        'operState',
                        'softwareDownloadState',
                        'name',
                    ]);

                    expect($onu['interface'])->toBeString()
                        ->toMatch('/^\d+\/\d+\/\d+$/');
                    expect($onu['onuId'])->toBeInt();
                    expect($onu['serialNumber'])->toBeString()
                        ->toMatch('/^[A-Z0-9]+$/');
                    expect($onu['operState'])->toBeString()
                        ->toBeIn(['Up', 'Down']);
                    expect($onu['softwareDownloadState'])->toBeString();
                    expect($onu['name'])->toBeString();
                }
            });
        });
    });
});
