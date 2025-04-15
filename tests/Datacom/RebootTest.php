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
});

describe('Datacom - Onts Reboot', function () {
    it('can reboot onus successfully', function () {
        $datacom = Datacom::connectTelnet($this->ipServer, $this->usernameTelnet, $this->passwordTelnet, 23);
        $datacom->interfaces(['1/1/3/1']);

        $result = $datacom->ontsReboot();

        dump($result->toArray());

        expect($result)
            ->toBeInstanceOf(Collection::class)
            ->not->toBeEmpty();

        $result->each(function ($batch) {
            expect($batch)
                ->toBeInstanceOf(CommandResultBatch::class)
                ->and($batch->commands)->toBeInstanceOf(Collection::class)
                ->and($batch->commands)->not->toBeEmpty();

            collect($batch->commands)->each(function ($commandResult) {
                expect($commandResult->success)->toBeTrue();
            });
        });
    });

    it('fails when interface is invalid', function () {
        $datacom = Datacom::connectTelnet($this->ipServer, $this->usernameTelnet, $this->passwordTelnet, 23);

        expect(fn () => $datacom->interfaces(['1/1/1:1']))
            ->toThrow(InvalidArgumentException::class, 'Invalid interface format');
    });

    it('handles empty ONT list', function () {
        $datacom = Datacom::connectTelnet($this->ipServer, $this->usernameTelnet, $this->passwordTelnet, 23);
        $datacom->interfaces([]);

        expect(fn () => $datacom->ontsAlarm())
            ->toThrow(Exception::class, 'Interface(s) not found');
    });
});
