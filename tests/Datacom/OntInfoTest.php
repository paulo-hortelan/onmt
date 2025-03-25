<?php

use Illuminate\Support\Collection;
use InvalidArgumentException;
use PauloHortelan\Onmt\Facades\Datacom;
use PauloHortelan\Onmt\Models\CommandResultBatch;

uses()->group('Datacom');

beforeEach(function () {
    $this->ipServer = env('DATACOM_DM4612_OLT_IP');
    $this->usernameTelnet = env('DATACOM_DM4612_OLT_USERNAME_TELNET');
    $this->passwordTelnet = env('DATACOM_DM4612_OLT_PASSWORD_TELNET');

    $this->interface = env('DATACOM_DM4612_INTERFACE_CMSZ');
});

describe('Datacom - Onts Info (RxPower, Uptime, etc.)', function () {
    it('can get onts info successfully', function () {
        $datacom = Datacom::connectTelnet($this->ipServer, $this->usernameTelnet, $this->passwordTelnet, 23);
        $datacom->interfaces([$this->interface]);

        $ontInfo = $datacom->ontsInfo();

        dump($ontInfo->toArray());

        expect($ontInfo)
            ->toBeInstanceOf(Collection::class)
            ->not->toBeEmpty();

        $ontInfo->each(function ($batch) {
            expect($batch)
                ->toBeInstanceOf(CommandResultBatch::class)
                ->and($batch->commands)->toBeInstanceOf(Collection::class)
                ->and($batch->commands)->not->toBeEmpty();

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue()
                    ->and($commandResult->result)->toHaveKeys([
                        'RxOpticalPower[dBm]',
                        'TxOpticalPower[dBm]',
                        'ID',
                        'LastSeenOnline',
                    ])
                    ->and($commandResult->result['RxOpticalPower[dBm]'])->toBeFloat()
                    ->and($commandResult->result['TxOpticalPower[dBm]'])->toBeFloat()
                    ->and($commandResult->result['ID'])->toBeInt();
            });
        });
    })->only();

    it('fails when interface is invalid', function () {
        $datacom = Datacom::connectTelnet($this->ipServer, $this->usernameTelnet, $this->passwordTelnet, 23);

        expect(fn () => $datacom->interfaces(['1/1/1:1']))
            ->toThrow(InvalidArgumentException::class, 'Invalid interface format');
    });

    it('handles empty ONT list', function () {
        $datacom = Datacom::connectTelnet($this->ipServer, $this->usernameTelnet, $this->passwordTelnet, 23);
        $datacom->interfaces([]);

        expect(fn () => $datacom->ontsInfo())
            ->toThrow(Exception::class, 'Interface(s) not found');
    });
});
