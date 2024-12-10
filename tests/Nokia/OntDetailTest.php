<?php

use PauloHortelan\Onmt\Facades\Nokia;

uses()->group('Nokia');

beforeEach(function () {
    $ipServer = env('NOKIA_OLT_IP');
    $username = env('NOKIA_OLT_USERNAME_TELNET');
    $password = env('NOKIA_OLT_PASSWORD_TELNET');

    $this->serialALCL = env('NOKIA_SERIAL_ALCL');
    $this->serialCMSZ = env('NOKIA_SERIAL_CMSZ');

    $this->interfaceALCL = env('NOKIA_INTERFACE_ALCL');
    $this->interfaceCMSZ = env('NOKIA_INTERFACE_CMSZ');

    $this->nokia = Nokia::connectTelnet($ipServer, $username, $password, 23);
});

describe('Nokia Optical Detail - Success', function () {
    it('can get single detail', function () {
        $this->nokia->interfaces([$this->interfaceALCL]);

        $ontsDetail = $this->nokia->ontsDetail();

        var_dump($ontsDetail);

        expect($ontsDetail)->toBeArray();
        expect($ontsDetail[0]['result']['rxSignalLevel'])->toBeFloat();
    });

    it('can get multiple details', function () {
        $this->nokia->interfaces([$this->interfaceALCL, $this->interfaceCMSZ]);

        $ontsDetail = $this->nokia->ontsDetail();

        expect($ontsDetail)->toBeArray();
        expect($ontsDetail[0]['result']['rxSignalLevel'])->toBeFloat();
        expect($ontsDetail[1]['result']['rxSignalLevel'])->toBeFloat();
        expect($ontsDetail[2]['result']['rxSignalLevel'])->toBeFloat();
    });
});

describe('Nokia Optical Detail By Serial - Success', function () {
    it('can get single detail', function () {
        $this->nokia->serials([$this->serialALCL]);

        $ontsDetail = $this->nokia->ontsDetailBySerials();

        var_dump($ontsDetail);

        expect($ontsDetail)->toBeArray();
        expect($ontsDetail[0]['result']['rxSignalLevel'])->toBeFloat();
    })->only();

    it('can get multiple details', function () {
        $this->nokia->serials([$this->serial1, $this->serial2, $this->serial3]);

        $details = $this->nokia->ontsDetailBySerials();

        expect($details)->toBeArray();
        expect($details[0]['result']['rxSignalLevel'])->toBeFloat();
        expect($details[1]['result']['rxSignalLevel'])->toBeFloat();
        expect($details[2]['result']['rxSignalLevel'])->toBeFloat();
    });
});
