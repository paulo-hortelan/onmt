<?php

use PauloHortelan\Onmt\Facades\Nokia;

uses()->group('Nokia');

beforeEach(function () {
    $ipServer = env('NOKIA_OLT_IP');
    $username = env('NOKIA_OLT_USERNAME');
    $password = env('NOKIA_OLT_PASSWORD');

    $this->serial1 = env('NOKIA_SERIAL_1');
    $this->serial2 = env('NOKIA_SERIAL_2');
    $this->serial3 = env('NOKIA_SERIAL_3');

    $this->interface1 = env('NOKIA_INTERFACE_1');
    $this->interface2 = env('NOKIA_INTERFACE_2');
    $this->interface3 = env('NOKIA_INTERFACE_3');

    $this->nokia = Nokia::connect($ipServer, $username, $password);
});

describe('Nokia Optical Detail - Success', function () {
    it('can get single detail', function () {
        $detail = $this->nokia->opticalDetails([$this->interface1]);

        expect($detail)->toBeArray();
        expect($detail[0]['result']['rxSignalLevel'])->toBeFloat();

        $detail = $this->nokia->interface($this->interface1)->opticalDetails();

        expect($detail)->toBeArray();
        expect($detail[0]['result']['rxSignalLevel'])->toBeFloat();
    });

    it('can get multiple details', function () {
        $interfaces = [$this->interface1, $this->interface2, $this->interface3];

        $details = $this->nokia->opticalDetails($interfaces);

        expect($details)->toBeArray();
        expect($details[0]['result']['rxSignalLevel'])->toBeFloat();
        expect($details[1]['result']['rxSignalLevel'])->toBeFloat();
        expect($details[2]['result']['rxSignalLevel'])->toBeFloat();

        $details = $this->nokia->interfaces($interfaces)->opticalDetails();

        expect($details)->toBeArray();
        expect($details[0]['result']['rxSignalLevel'])->toBeFloat();
        expect($details[1]['result']['rxSignalLevel'])->toBeFloat();
        expect($details[2]['result']['rxSignalLevel'])->toBeFloat();
    });
});

describe('Nokia Optical Detail By Serial - Success', function () {
    it('can get single detail', function () {
        $detail = $this->nokia->opticalDetailsBySerials([$this->serial1]);

        expect($detail)->toBeArray();
        expect($detail[0]['result']['rxSignalLevel'])->toBeFloat();

        $detail = $this->nokia->serial($this->serial1)->opticalDetailsBySerials();

        expect($detail)->toBeArray();
        expect($detail[0]['result']['rxSignalLevel'])->toBeFloat();
    });

    it('can get multiple details', function () {
        $serials = [$this->serial1, $this->serial2, $this->serial3];

        $details = $this->nokia->opticalDetailsBySerials($serials);

        expect($details)->toBeArray();
        expect($details[0]['result']['rxSignalLevel'])->toBeFloat();
        expect($details[1]['result']['rxSignalLevel'])->toBeFloat();
        expect($details[2]['result']['rxSignalLevel'])->toBeFloat();

        $details = $this->nokia->serials($serials)->opticalDetailsBySerials();

        expect($details)->toBeArray();
        expect($details[0]['result']['rxSignalLevel'])->toBeFloat();
        expect($details[1]['result']['rxSignalLevel'])->toBeFloat();
        expect($details[2]['result']['rxSignalLevel'])->toBeFloat();

        $serials = [$this->serial1, $this->serial2, 'ALCLFC000000'];

        $details = $this->nokia->opticalDetailsBySerials($serials);

        expect($details)->toBeArray();
        expect($details[0]['result']['rxSignalLevel'])->toBeFloat();
        expect($details[1]['result']['rxSignalLevel'])->toBeFloat();
        expect($details[2]['success'])->toBeFalse();

        $details = $this->nokia->serials($serials)->opticalDetailsBySerials();

        expect($details)->toBeArray();
        expect($details[0]['result']['rxSignalLevel'])->toBeFloat();
        expect($details[1]['result']['rxSignalLevel'])->toBeFloat();
        expect($details[2]['success'])->toBeFalse();
    });
});

afterAll(function () {
    $this->nokia->disconnect();
});
